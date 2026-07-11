<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260711154000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align CardLab and Quardlock enrollment indexes with Doctrine metadata.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE connected_cards CHANGE quardlock_enrollment_status quardlock_enrollment_status VARCHAR(40) NOT NULL');
        $this->addSql('ALTER TABLE connected_cards RENAME INDEX UNIQ_CONNECTED_CARDLAB_IDENTIFIER TO UNIQ_579BA6153643EE6');
        $this->addSql('ALTER TABLE connected_cards RENAME INDEX UNIQ_CONNECTED_QUARDLOCK_TOKEN TO UNIQ_579BA6159ECE477');
        $this->addSql('ALTER TABLE quardlock_audit_logs RENAME INDEX IDX_QUARDLOCK_AUDIT_CARD TO IDX_5FCDF6F8D93CDDD9');
        $this->addSql('ALTER TABLE quardlock_audit_logs RENAME INDEX IDX_EE18CBC0D7B22588 TO IDX_5FCDF6F8C4EF1FC7');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE connected_cards CHANGE quardlock_enrollment_status quardlock_enrollment_status VARCHAR(40) NOT NULL DEFAULT 'not_started'");
        $this->addSql('ALTER TABLE connected_cards RENAME INDEX UNIQ_579BA6153643EE6 TO UNIQ_CONNECTED_CARDLAB_IDENTIFIER');
        $this->addSql('ALTER TABLE connected_cards RENAME INDEX UNIQ_579BA6159ECE477 TO UNIQ_CONNECTED_QUARDLOCK_TOKEN');
        $this->addSql('ALTER TABLE quardlock_audit_logs RENAME INDEX IDX_5FCDF6F8D93CDDD9 TO IDX_QUARDLOCK_AUDIT_CARD');
        $this->addSql('ALTER TABLE quardlock_audit_logs RENAME INDEX IDX_5FCDF6F8C4EF1FC7 TO IDX_EE18CBC0D7B22588');
    }
}
