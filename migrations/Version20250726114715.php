<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250726114715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guest ADD client_id INT NOT NULL');
        $this->addSql('ALTER TABLE guest ADD CONSTRAINT FK_ACB79A3519EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_ACB79A3519EB6921 ON guest (client_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guest DROP FOREIGN KEY FK_ACB79A3519EB6921');
        $this->addSql('DROP INDEX IDX_ACB79A3519EB6921 ON guest');
        $this->addSql('ALTER TABLE guest DROP client_id');
    }
}
