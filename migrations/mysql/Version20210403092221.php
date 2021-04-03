<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210403092221 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE interested SET last_edited_at = created_at  WHERE ISNULL(last_edited_at)');

        $this->addSql('ALTER TABLE interested CHANGE last_edited_at last_edited_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_class ADD manage_req INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE interested CHANGE last_edited_at last_edited_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user_class DROP manage_req');
    }
}
