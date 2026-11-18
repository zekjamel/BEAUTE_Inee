<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260930120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Catalogue administrable et conservation des modalités achetées ; initialise la carte à son tarif existant.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products ADD description LONGTEXT DEFAULT NULL, ADD diagnostic_count INT DEFAULT 0 NOT NULL, ADD delivery_options JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_orders ADD fulfillment JSON DEFAULT NULL');
        $options = json_encode(['shipping' => ['label' => 'Livraison à domicile', 'amount' => 700,
            'terms' => 'Livraison à l’adresse indiquée lors de la commande.', 'countries' => ['FR', 'BE', 'CH']]], JSON_THROW_ON_ERROR);
        $this->addSql('INSERT INTO products (sku, name, type, unit_amount_cents, currency, is_active, description, diagnostic_count, delivery_options, created_at, updated_at) SELECT ?, ?, ?, 7000, ?, 1, ?, 0, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP WHERE NOT EXISTS (SELECT 1 FROM products WHERE sku = ?)', [
            'BI-CARTE', 'Carte connectée Beauté INÉE', 'card', 'EUR',
            'L’activation de la carte nécessite un rendez-vous en boutique après le paiement.', $options, 'BI-CARTE',
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Les modalités achetées doivent être conservées avec les commandes.');
    }
}
