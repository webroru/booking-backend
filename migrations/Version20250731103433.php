<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250731103433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beds24_token RENAME INDEX uniq_5f37a13b19eb6921 TO UNIQ_70146CDD19EB6921');
        $this->addSql('ALTER TABLE client ADD admin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455642B8210 FOREIGN KEY (admin_id) REFERENCES `admin` (id)');
        $this->addSql('CREATE INDEX IDX_C7440455642B8210 ON client (admin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beds24_token RENAME INDEX uniq_70146cdd19eb6921 TO UNIQ_5F37A13B19EB6921');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455642B8210');
        $this->addSql('DROP INDEX IDX_C7440455642B8210 ON client');
        $this->addSql('ALTER TABLE client DROP admin_id');
    }
}
