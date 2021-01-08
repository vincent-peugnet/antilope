<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201216235345 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_class (id INT AUTO_INCREMENT NOT NULL, rank SMALLINT NOT NULL, name VARCHAR(32) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD user_class_id INT NOT NULL, ADD created_at DATETIME NOT NULL, ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A8DEF10 FOREIGN KEY (user_class_id) REFERENCES user_class (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649A8DEF10 ON user (user_class_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A8DEF10');
        $this->addSql('DROP TABLE user_class');
        $this->addSql('DROP INDEX IDX_8D93D649A8DEF10 ON user');
        $this->addSql('ALTER TABLE user DROP user_class_id, DROP created_at, DROP description');
    }
}
