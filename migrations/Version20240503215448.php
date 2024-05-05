<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240503215448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE anime ADD format_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ADD season_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ADD status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ADD average_score INT DEFAULT NULL');
        $this->addSql('ALTER TABLE anime ADD CONSTRAINT FK_13045942D629F605 FOREIGN KEY (format_id) REFERENCES format (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE anime ADD CONSTRAINT FK_130459424EC001D1 FOREIGN KEY (season_id) REFERENCES season (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE anime ADD CONSTRAINT FK_130459426BF700BD FOREIGN KEY (status_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_13045942D629F605 ON anime (format_id)');
        $this->addSql('CREATE INDEX IDX_130459424EC001D1 ON anime (season_id)');
        $this->addSql('CREATE INDEX IDX_130459426BF700BD ON anime (status_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE anime DROP CONSTRAINT FK_13045942D629F605');
        $this->addSql('ALTER TABLE anime DROP CONSTRAINT FK_130459424EC001D1');
        $this->addSql('ALTER TABLE anime DROP CONSTRAINT FK_130459426BF700BD');
        $this->addSql('DROP INDEX IDX_13045942D629F605');
        $this->addSql('DROP INDEX IDX_130459424EC001D1');
        $this->addSql('DROP INDEX IDX_130459426BF700BD');
        $this->addSql('ALTER TABLE anime DROP format_id');
        $this->addSql('ALTER TABLE anime DROP season_id');
        $this->addSql('ALTER TABLE anime DROP status_id');
        $this->addSql('ALTER TABLE anime DROP average_score');
    }
}
