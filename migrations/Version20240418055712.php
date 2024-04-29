<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240418051609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE anime ADD trailer_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ADD trailer_img VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ADD year INT DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ADD episodes INT DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ADD synopsis TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ALTER mal_id SET DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE anime DROP trailer_url');
        $this->addSql('ALTER TABLE anime DROP trailer_img');
        $this->addSql('ALTER TABLE anime DROP year');
        $this->addSql('ALTER TABLE anime DROP episodes');
        $this->addSql('ALTER TABLE anime DROP synopsis');
        $this->addSql('ALTER TABLE anime ALTER mal_id DROP NOT NULL');
    }
}
