<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250815123042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM guest');
        $this->addSql('CREATE TABLE room (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, number VARCHAR(255) NOT NULL, external_id INT NOT NULL, government_portal_id INT DEFAULT NULL, INDEX IDX_729F519B19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519B19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guest ADD room_id INT NOT NULL, DROP room');
        $this->addSql('ALTER TABLE guest ADD CONSTRAINT FK_ACB79A3554177093 FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_ACB79A3554177093 ON guest (room_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guest DROP FOREIGN KEY FK_ACB79A3554177093');
        $this->addSql('ALTER TABLE room DROP FOREIGN KEY FK_729F519B19EB6921');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP INDEX IDX_ACB79A3554177093 ON guest');
        $this->addSql('ALTER TABLE guest ADD room VARCHAR(255) NOT NULL, DROP room_id');
    }
}
