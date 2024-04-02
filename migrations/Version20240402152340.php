<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240402152340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE liste_anime (liste_id INT NOT NULL, anime_id INT NOT NULL, PRIMARY KEY(liste_id, anime_id))');
        $this->addSql('CREATE INDEX IDX_CFF8DECE85441D8 ON liste_anime (liste_id)');
        $this->addSql('CREATE INDEX IDX_CFF8DEC794BBE89 ON liste_anime (anime_id)');
        $this->addSql('ALTER TABLE liste_anime ADD CONSTRAINT FK_CFF8DECE85441D8 FOREIGN KEY (liste_id) REFERENCES liste (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE liste_anime ADD CONSTRAINT FK_CFF8DEC794BBE89 FOREIGN KEY (anime_id) REFERENCES anime (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE liste_anime DROP CONSTRAINT FK_CFF8DECE85441D8');
        $this->addSql('ALTER TABLE liste_anime DROP CONSTRAINT FK_CFF8DEC794BBE89');
        $this->addSql('DROP TABLE liste_anime');
    }
}
