<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260711155000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align existing relation indexes with the current Doctrine metadata.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE connected_cards RENAME INDEX IDX_579BA61A15A2E17 TO IDX_579BA61CEBF5BEA');
        $this->addSql('ALTER TABLE email_logs RENAME INDEX IDX_8C2F8EA9395C3F3 TO IDX_6FB9E6D99395C3F3');
        $this->addSql('ALTER TABLE email_logs RENAME INDEX IDX_8C2F8EA9A76ED395 TO IDX_6FB9E6D9A76ED395');
        $this->addSql('ALTER TABLE email_logs RENAME INDEX IDX_8C2F8EA927C4C46 TO IDX_6FB9E6D9A15A2E17');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE connected_cards RENAME INDEX IDX_579BA61CEBF5BEA TO IDX_579BA61A15A2E17');
        $this->addSql('ALTER TABLE email_logs RENAME INDEX IDX_6FB9E6D99395C3F3 TO IDX_8C2F8EA9395C3F3');
        $this->addSql('ALTER TABLE email_logs RENAME INDEX IDX_6FB9E6D9A76ED395 TO IDX_8C2F8EA9A76ED395');
        $this->addSql('ALTER TABLE email_logs RENAME INDEX IDX_6FB9E6D9A15A2E17 TO IDX_8C2F8EA927C4C46');
    }
}
