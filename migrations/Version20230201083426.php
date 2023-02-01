<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230201083426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE info (id INT AUTO_INCREMENT NOT NULL, hotel_name VARCHAR(255) NOT NULL, address VARCHAR(1024) NOT NULL, rules LONGTEXT DEFAULT NULL, checkout_info LONGTEXT DEFAULT NULL, call_time VARCHAR(255) DEFAULT NULL, contact_information VARCHAR(255) DEFAULT NULL, how_to_make_it LONGTEXT DEFAULT NULL, facilities LONGTEXT DEFAULT NULL, extras LONGTEXT DEFAULT NULL, instruction LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client ADD info_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404555D8BC1F8 FOREIGN KEY (info_id) REFERENCES info (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74404555D8BC1F8 ON client (info_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C74404555D8BC1F8');
        $this->addSql('DROP TABLE info');
        $this->addSql('DROP INDEX UNIQ_C74404555D8BC1F8 ON client');
        $this->addSql('ALTER TABLE client DROP info_id');
    }
}
