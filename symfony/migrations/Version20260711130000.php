<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260711130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link connected cards to their source order and record collection and setup milestones.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE connected_cards ADD source_order_id INT DEFAULT NULL, ADD collected_at DATETIME DEFAULT NULL, ADD initialized_at DATETIME DEFAULT NULL, ADD configured_at DATETIME DEFAULT NULL, ADD INDEX IDX_579BA61A15A2E17 (source_order_id)');
        $this->addSql('ALTER TABLE connected_cards ADD CONSTRAINT FK_579BA61A15A2E17 FOREIGN KEY (source_order_id) REFERENCES customer_orders (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE connected_cards DROP FOREIGN KEY FK_579BA61A15A2E17');
        $this->addSql('ALTER TABLE connected_cards DROP INDEX IDX_579BA61A15A2E17, DROP source_order_id, DROP collected_at, DROP initialized_at, DROP configured_at');
    }
}
