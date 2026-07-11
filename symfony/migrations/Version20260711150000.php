<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260711150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store non-sensitive Quardlock integration audit events.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE quardlock_audit_logs (id INT AUTO_INCREMENT NOT NULL, initiated_by_id INT DEFAULT NULL, action VARCHAR(80) NOT NULL, status VARCHAR(30) NOT NULL, endpoint VARCHAR(160) DEFAULT NULL, http_status INT DEFAULT NULL, message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_EE18CBC0D7B22588 (initiated_by_id), INDEX IDX_QUARDLOCK_AUDIT_STATUS (status), INDEX IDX_QUARDLOCK_AUDIT_CREATED (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE quardlock_audit_logs ADD CONSTRAINT FK_EE18CBC0D7B22588 FOREIGN KEY (initiated_by_id) REFERENCES users (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quardlock_audit_logs DROP FOREIGN KEY FK_EE18CBC0D7B22588');
        $this->addSql('DROP TABLE quardlock_audit_logs');
    }
}
