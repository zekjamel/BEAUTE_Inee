<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260710142500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align account activation token index names with Doctrine metadata.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE account_activation_tokens RENAME INDEX UNIQ_4B45BDE0E5D96BE3 TO UNIQ_40073328B3BC57DA');
        $this->addSql('ALTER TABLE account_activation_tokens RENAME INDEX IDX_4B45BDE0A76ED395 TO IDX_40073328A76ED395');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE account_activation_tokens RENAME INDEX UNIQ_40073328B3BC57DA TO UNIQ_4B45BDE0E5D96BE3');
        $this->addSql('ALTER TABLE account_activation_tokens RENAME INDEX IDX_40073328A76ED395 TO IDX_4B45BDE0A76ED395');
    }
}
