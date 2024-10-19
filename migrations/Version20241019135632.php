<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241019135632 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $sql = 'SELECT *
            FROM token t
            JOIN client c ON t.id = c.token_id';

        $rows = $this->connection->fetchAllAssociative($sql);

        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C744045541DEE7B9');
        $this->addSql('DROP INDEX UNIQ_C744045541DEE7B9 ON client');
        $this->addSql('DELETE FROM token');
        $this->addSql('ALTER TABLE client DROP token_id');
        $this->addSql('RENAME TABLE token TO beds24_token');
        $this->addSql('ALTER TABLE beds24_token ADD client_id INT NOT NULL');
        $this->addSql('ALTER TABLE beds24_token ADD CONSTRAINT FK_5F37A13B19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5F37A13B19EB6921 ON beds24_token (client_id)');

        foreach ($rows as $row) {
            $this->addSql('INSERT INTO beds24_token (token, refresh_token, expires_at, client_id)
                VALUES (:token, :refresh_token, :expires_at, :client_id)', [
                'token' => $row['token'],
                'refresh_token' => $row['refresh_token'],
                'expires_at' => $row['expires_at'],
                'client_id' => $row['id'],
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        $rows = $this->connection->fetchAllAssociative('SELECT * FROM beds24_token');

        $this->addSql('RENAME TABLE beds24_token TO token');
        $this->addSql('ALTER TABLE client ADD token_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C744045541DEE7B9 FOREIGN KEY (token_id) REFERENCES token (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C744045541DEE7B9 ON client (token_id)');
        $this->addSql('ALTER TABLE token DROP FOREIGN KEY FK_5F37A13B19EB6921');
        $this->addSql('DROP INDEX UNIQ_5F37A13B19EB6921 ON token');
        $this->addSql('ALTER TABLE token DROP client_id');

        foreach ($rows as $row) {
            $this->addSql('UPDATE client 
                SET token_id = :token_id
                WHERE id = :id', [
                'token_id' => $row['id'],
                'id' => $row['client_id'],
            ]);
        }
    }
}
