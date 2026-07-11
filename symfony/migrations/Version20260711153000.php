<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260711153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link physical CardLab cards and Quardlock enrollment state to connected cards.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE connected_cards ADD card_lab_identifier VARCHAR(120) DEFAULT NULL, ADD quardlock_token_serial_number VARCHAR(120) DEFAULT NULL, ADD quardlock_enrollment_status VARCHAR(40) NOT NULL DEFAULT 'not_started', ADD quardlock_enrollment_started_at DATETIME DEFAULT NULL, ADD quardlock_enrolled_at DATETIME DEFAULT NULL, ADD quardlock_enrollment_nonce_hash VARCHAR(64) DEFAULT NULL, ADD quardlock_enrollment_expires_at DATETIME DEFAULT NULL");
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CONNECTED_CARDLAB_IDENTIFIER ON connected_cards (card_lab_identifier)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CONNECTED_QUARDLOCK_TOKEN ON connected_cards (quardlock_token_serial_number)');
        $this->addSql('ALTER TABLE quardlock_audit_logs ADD connected_card_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_QUARDLOCK_AUDIT_CARD ON quardlock_audit_logs (connected_card_id)');
        $this->addSql('ALTER TABLE quardlock_audit_logs ADD CONSTRAINT FK_QUARDLOCK_AUDIT_CARD FOREIGN KEY (connected_card_id) REFERENCES connected_cards (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quardlock_audit_logs DROP FOREIGN KEY FK_QUARDLOCK_AUDIT_CARD');
        $this->addSql('DROP INDEX IDX_QUARDLOCK_AUDIT_CARD ON quardlock_audit_logs');
        $this->addSql('ALTER TABLE quardlock_audit_logs DROP connected_card_id');
        $this->addSql('DROP INDEX UNIQ_CONNECTED_CARDLAB_IDENTIFIER ON connected_cards');
        $this->addSql('DROP INDEX UNIQ_CONNECTED_QUARDLOCK_TOKEN ON connected_cards');
        $this->addSql('ALTER TABLE connected_cards DROP card_lab_identifier, DROP quardlock_token_serial_number, DROP quardlock_enrollment_status, DROP quardlock_enrollment_started_at, DROP quardlock_enrolled_at, DROP quardlock_enrollment_nonce_hash, DROP quardlock_enrollment_expires_at');
    }
}
