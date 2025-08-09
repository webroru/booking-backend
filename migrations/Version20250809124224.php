<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250809124224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD aj_pes_username VARCHAR(255) NOT NULL, ADD aj_pes_password VARCHAR(255) NOT NULL, CHANGE check_in_time check_in_time TIME NOT NULL');
        $this->addSql('ALTER TABLE guest CHANGE registration_date registration_date DATETIME NOT NULL, CHANGE check_in_date check_in_date DATE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP aj_pes_username, DROP aj_pes_password, CHANGE check_in_time check_in_time TIME DEFAULT \'12:00:00\' NOT NULL');
        $this->addSql('ALTER TABLE guest CHANGE registration_date registration_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE check_in_date check_in_date DATE DEFAULT \'2025-08-07\' NOT NULL');
    }
}
