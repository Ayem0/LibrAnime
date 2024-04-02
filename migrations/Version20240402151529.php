<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240402151529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE anime_categorie (anime_id INT NOT NULL, categorie_id INT NOT NULL, PRIMARY KEY(anime_id, categorie_id))');
        $this->addSql('CREATE INDEX IDX_9223DF30794BBE89 ON anime_categorie (anime_id)');
        $this->addSql('CREATE INDEX IDX_9223DF30BCF5E72D ON anime_categorie (categorie_id)');
        $this->addSql('ALTER TABLE anime_categorie ADD CONSTRAINT FK_9223DF30794BBE89 FOREIGN KEY (anime_id) REFERENCES anime (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE anime_categorie ADD CONSTRAINT FK_9223DF30BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE anime_categorie DROP CONSTRAINT FK_9223DF30794BBE89');
        $this->addSql('ALTER TABLE anime_categorie DROP CONSTRAINT FK_9223DF30BCF5E72D');
        $this->addSql('DROP TABLE anime_categorie');
    }
}
