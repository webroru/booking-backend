<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250726090910 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beds24_token RENAME INDEX uniq_5f37a13b19eb6921 TO UNIQ_70146CDD19EB6921');
        $this->addSql('ALTER TABLE guest ADD referer VARCHAR(255) NOT NULL, ADD room VARCHAR(255) NOT NULL, ADD is_reported TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE beds24_token RENAME INDEX uniq_70146cdd19eb6921 TO UNIQ_5F37A13B19EB6921');
        $this->addSql('ALTER TABLE guest DROP referer, DROP room, DROP is_reported');
    }
}
