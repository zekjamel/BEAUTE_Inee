<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260710141500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add account activation tokens for post-payment account setup.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE account_activation_tokens (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token_hash VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL, used_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_4B45BDE0E5D96BE3 (token_hash), INDEX IDX_4B45BDE0A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE account_activation_tokens ADD CONSTRAINT FK_4B45BDE0A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE account_activation_tokens DROP FOREIGN KEY FK_4B45BDE0A76ED395');
        $this->addSql('DROP TABLE account_activation_tokens');
    }
}
