<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230217105714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C74404555D8BC1F8');
        $this->addSql('DROP INDEX UNIQ_C74404555D8BC1F8 ON client');
        $this->addSql('ALTER TABLE client DROP info_id');
        $this->addSql('ALTER TABLE info ADD client_id INT NOT NULL, ADD locale VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE info ADD CONSTRAINT FK_CB89315719EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_CB89315719EB6921 ON info (client_id)');
        $this->addSql('CREATE UNIQUE INDEX client_locale ON info (client_id, locale)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD info_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C74404555D8BC1F8 FOREIGN KEY (info_id) REFERENCES info (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C74404555D8BC1F8 ON client (info_id)');
        $this->addSql('ALTER TABLE info DROP FOREIGN KEY FK_CB89315719EB6921');
        $this->addSql('DROP INDEX IDX_CB89315719EB6921 ON info');
        $this->addSql('DROP INDEX client_locale ON info');
        $this->addSql('ALTER TABLE info DROP client_id, DROP locale');
    }
}
