<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260711143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Track transactional email delivery attempts.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE email_logs (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, user_id INT DEFAULT NULL, customer_order_id INT DEFAULT NULL, type VARCHAR(80) NOT NULL, recipient VARCHAR(180) NOT NULL, sender VARCHAR(180) NOT NULL, subject VARCHAR(190) NOT NULL, status VARCHAR(30) NOT NULL, sent_at DATETIME DEFAULT NULL, error_message LONGTEXT DEFAULT NULL, context JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_8C2F8EA9395C3F3 (customer_id), INDEX IDX_8C2F8EA9A76ED395 (user_id), INDEX IDX_8C2F8EA927C4C46 (customer_order_id), INDEX IDX_EMAIL_LOG_STATUS (status), INDEX IDX_EMAIL_LOG_RECIPIENT (recipient), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE email_logs ADD CONSTRAINT FK_8C2F8EA9395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE email_logs ADD CONSTRAINT FK_8C2F8EA9A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE email_logs ADD CONSTRAINT FK_8C2F8EA927C4C46 FOREIGN KEY (customer_order_id) REFERENCES customer_orders (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE email_logs DROP FOREIGN KEY FK_8C2F8EA9395C3F3');
        $this->addSql('ALTER TABLE email_logs DROP FOREIGN KEY FK_8C2F8EA9A76ED395');
        $this->addSql('ALTER TABLE email_logs DROP FOREIGN KEY FK_8C2F8EA927C4C46');
        $this->addSql('DROP TABLE email_logs');
    }
}
