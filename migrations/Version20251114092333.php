<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114092333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE guide (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, experience_years INTEGER NOT NULL, is_active BOOLEAN NOT NULL)');
        $this->addSql('CREATE INDEX IDX_guide_active ON guide (is_active)');
        $this->addSql('CREATE INDEX IDX_guide_experience ON guide (experience_years)');
        $this->addSql('CREATE TABLE hunting_booking (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, guide_id INTEGER NOT NULL, tour_name VARCHAR(255) NOT NULL, hunter_name VARCHAR(255) NOT NULL, date DATE NOT NULL --(DC2Type:date_immutable)
        , participants_count INTEGER NOT NULL, CONSTRAINT FK_CA229817D7ED1D4B FOREIGN KEY (guide_id) REFERENCES guide (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_CA229817D7ED1D4B ON hunting_booking (guide_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_booking_guide_date ON hunting_booking (guide_id, date)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE guide');
        $this->addSql('DROP TABLE hunting_booking');
    }
}
