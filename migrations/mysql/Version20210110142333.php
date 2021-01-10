<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210110142333 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sharable_contact (id INT AUTO_INCREMENT NOT NULL, sharable_id INT NOT NULL, created_at DATETIME NOT NULL, type VARCHAR(64) NOT NULL, content VARCHAR(255) NOT NULL, info LONGTEXT DEFAULT NULL, INDEX IDX_67B095EDCADDAACF (sharable_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sharable_contact ADD CONSTRAINT FK_67B095EDCADDAACF FOREIGN KEY (sharable_id) REFERENCES sharable (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sharable_contact');
    }
}
