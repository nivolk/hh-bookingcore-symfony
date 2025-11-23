<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251122113856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE guide (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, experience_years INT NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_guide_active (is_active), INDEX IDX_guide_experience (experience_years), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hunting_booking (id INT AUTO_INCREMENT NOT NULL, guide_id INT NOT NULL, tour_name VARCHAR(255) NOT NULL, hunter_name VARCHAR(255) NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', participants_count INT NOT NULL, INDEX IDX_CA229817D7ED1D4B (guide_id), UNIQUE INDEX UNIQ_booking_guide_date (guide_id, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE hunting_booking ADD CONSTRAINT FK_CA229817D7ED1D4B FOREIGN KEY (guide_id) REFERENCES guide (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hunting_booking DROP FOREIGN KEY FK_CA229817D7ED1D4B');
        $this->addSql('DROP TABLE guide');
        $this->addSql('DROP TABLE hunting_booking');
    }
}
