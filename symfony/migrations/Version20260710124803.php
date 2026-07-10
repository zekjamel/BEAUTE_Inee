<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260710124803 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointments (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, status VARCHAR(40) NOT NULL, scheduled_at DATETIME NOT NULL, completed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, customer_id INT NOT NULL, center_id INT DEFAULT NULL, INDEX IDX_6A41727A9395C3F3 (customer_id), INDEX IDX_6A41727A5932F377 (center_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE centers (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(180) NOT NULL, type VARCHAR(30) NOT NULL, address_line1 VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(20) DEFAULT NULL, city VARCHAR(120) DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_274DDB3177153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE connected_cards (id INT AUTO_INCREMENT NOT NULL, external_identifier VARCHAR(120) NOT NULL, status VARCHAR(40) NOT NULL, ordered_at DATETIME DEFAULT NULL, activated_at DATETIME DEFAULT NULL, provider VARCHAR(80) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, customer_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_579BA616DD00CB8 (external_identifier), INDEX IDX_579BA619395C3F3 (customer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE consents (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, is_granted TINYINT NOT NULL, source VARCHAR(50) DEFAULT NULL, policy_version VARCHAR(50) DEFAULT NULL, recorded_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, customer_id INT NOT NULL, INDEX IDX_6DACD679395C3F3 (customer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE customer_orders (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(40) NOT NULL, status VARCHAR(40) NOT NULL, total_amount_cents INT NOT NULL, currency VARCHAR(3) NOT NULL, shipping_address JSON DEFAULT NULL, paid_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, customer_id INT NOT NULL, UNIQUE INDEX UNIQ_54EA21BFAEA34913 (reference), INDEX IDX_54EA21BF9395C3F3 (customer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE customers (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(120) NOT NULL, last_name VARCHAR(120) NOT NULL, email VARCHAR(180) DEFAULT NULL, phone VARCHAR(32) DEFAULT NULL, birth_day INT DEFAULT NULL, birth_month INT DEFAULT NULL, birth_year INT DEFAULT NULL, age_range VARCHAR(30) DEFAULT NULL, preferred_locale VARCHAR(5) NOT NULL, status VARCHAR(40) NOT NULL, acquisition_channel VARCHAR(60) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, acquisition_center_id INT DEFAULT NULL, reference_center_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_62534E21E7927C74 (email), INDEX IDX_62534E21BAB0592D (acquisition_center_id), INDEX IDX_62534E2199509581 (reference_center_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE diagnostics (id INT AUTO_INCREMENT NOT NULL, performed_at DATETIME NOT NULL, status VARCHAR(50) NOT NULL, external_reference VARCHAR(100) DEFAULT NULL, skin_type VARCHAR(80) DEFAULT NULL, skin_conditions JSON DEFAULT NULL, priority_objectives JSON DEFAULT NULL, global_score INT DEFAULT NULL, recommendations JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, customer_id INT DEFAULT NULL, center_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_8F9088078AF8E607 (external_reference), INDEX IDX_8F9088079395C3F3 (customer_id), INDEX IDX_8F9088075932F377 (center_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_items (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(180) NOT NULL, quantity INT NOT NULL, unit_amount_cents INT NOT NULL, customer_order_id INT NOT NULL, product_id INT DEFAULT NULL, INDEX IDX_62809DB0A15A2E17 (customer_order_id), INDEX IDX_62809DB04584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE payments (id INT AUTO_INCREMENT NOT NULL, provider VARCHAR(30) NOT NULL, provider_session_id VARCHAR(255) NOT NULL, status VARCHAR(40) NOT NULL, amount_cents INT NOT NULL, currency VARCHAR(3) NOT NULL, confirmed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, customer_order_id INT NOT NULL, UNIQUE INDEX UNIQ_65D29B324489A067 (provider_session_id), INDEX IDX_65D29B32A15A2E17 (customer_order_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE products (id INT AUTO_INCREMENT NOT NULL, sku VARCHAR(100) NOT NULL, name VARCHAR(180) NOT NULL, type VARCHAR(40) NOT NULL, unit_amount_cents INT NOT NULL, currency VARCHAR(3) NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_B3BA5A5AF9038C4 (sku), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, customer_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), UNIQUE INDEX UNIQ_1483A5E99395C3F3 (customer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appointments ADD CONSTRAINT FK_6A41727A5932F377 FOREIGN KEY (center_id) REFERENCES centers (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE connected_cards ADD CONSTRAINT FK_579BA619395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE consents ADD CONSTRAINT FK_6DACD679395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer_orders ADD CONSTRAINT FK_54EA21BF9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE customers ADD CONSTRAINT FK_62534E21BAB0592D FOREIGN KEY (acquisition_center_id) REFERENCES centers (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE customers ADD CONSTRAINT FK_62534E2199509581 FOREIGN KEY (reference_center_id) REFERENCES centers (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE diagnostics ADD CONSTRAINT FK_8F9088079395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE diagnostics ADD CONSTRAINT FK_8F9088075932F377 FOREIGN KEY (center_id) REFERENCES centers (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB0A15A2E17 FOREIGN KEY (customer_order_id) REFERENCES customer_orders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB04584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE payments ADD CONSTRAINT FK_65D29B32A15A2E17 FOREIGN KEY (customer_order_id) REFERENCES customer_orders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E99395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A9395C3F3');
        $this->addSql('ALTER TABLE appointments DROP FOREIGN KEY FK_6A41727A5932F377');
        $this->addSql('ALTER TABLE connected_cards DROP FOREIGN KEY FK_579BA619395C3F3');
        $this->addSql('ALTER TABLE consents DROP FOREIGN KEY FK_6DACD679395C3F3');
        $this->addSql('ALTER TABLE customer_orders DROP FOREIGN KEY FK_54EA21BF9395C3F3');
        $this->addSql('ALTER TABLE customers DROP FOREIGN KEY FK_62534E21BAB0592D');
        $this->addSql('ALTER TABLE customers DROP FOREIGN KEY FK_62534E2199509581');
        $this->addSql('ALTER TABLE diagnostics DROP FOREIGN KEY FK_8F9088079395C3F3');
        $this->addSql('ALTER TABLE diagnostics DROP FOREIGN KEY FK_8F9088075932F377');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB0A15A2E17');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB04584665A');
        $this->addSql('ALTER TABLE payments DROP FOREIGN KEY FK_65D29B32A15A2E17');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E99395C3F3');
        $this->addSql('DROP TABLE appointments');
        $this->addSql('DROP TABLE centers');
        $this->addSql('DROP TABLE connected_cards');
        $this->addSql('DROP TABLE consents');
        $this->addSql('DROP TABLE customer_orders');
        $this->addSql('DROP TABLE customers');
        $this->addSql('DROP TABLE diagnostics');
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE payments');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE users');
    }
}
