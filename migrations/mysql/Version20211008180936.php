<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211008180936 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report ADD validation_id INT DEFAULT NULL, CHANGE sharable_id sharable_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784A2274850 FOREIGN KEY (validation_id) REFERENCES validation (id)');
        $this->addSql('CREATE INDEX IDX_C42F7784A2274850 ON report (validation_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F7784A2274850');
        $this->addSql('DROP INDEX IDX_C42F7784A2274850 ON report');
        $this->addSql('ALTER TABLE report DROP validation_id, CHANGE sharable_id sharable_id INT NOT NULL');
    }
}
