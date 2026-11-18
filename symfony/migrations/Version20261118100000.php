<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261118100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Jetons de réinitialisation de mot de passe.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE password_reset_tokens (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token_hash VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL, used_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_3967A216B3BC57DA (token_hash), INDEX IDX_3967A216A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE password_reset_tokens ADD CONSTRAINT FK_3967A216A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE password_reset_tokens DROP FOREIGN KEY FK_3967A216A76ED395');
        $this->addSql('DROP TABLE password_reset_tokens');
    }
}
