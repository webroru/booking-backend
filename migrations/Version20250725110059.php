<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250725110059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE guest 
                (id INT AUTO_INCREMENT NOT NULL,
                 booking_id INT NOT NULL,
                 first_name VARCHAR(255) NOT NULL,
                 last_name VARCHAR(255) NOT NULL,
                 document_number VARCHAR(255) NOT NULL,
                 document_type VARCHAR(255) NOT NULL,
                 date_of_birth DATE NOT NULL,
                 nationality VARCHAR(2) NOT NULL,
                 gender VARCHAR(255) NOT NULL,
                 check_out_date DATE NOT NULL,
                 check_out_time TIME NOT NULL,
                 city_tax_exemption INT NOT NULL,
                 PRIMARY KEY(id))
            DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE guest');
    }
}
