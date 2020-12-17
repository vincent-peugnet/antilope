<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201217133407 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sharable_user (sharable_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_9E9F8B75CADDAACF (sharable_id), INDEX IDX_9E9F8B75A76ED395 (user_id), PRIMARY KEY(sharable_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sharable_user ADD CONSTRAINT FK_9E9F8B75CADDAACF FOREIGN KEY (sharable_id) REFERENCES sharable (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sharable_user ADD CONSTRAINT FK_9E9F8B75A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sharable ADD created_at DATETIME NOT NULL, ADD begin_at DATETIME DEFAULT NULL, ADD end_at DATETIME DEFAULT NULL, ADD disabled TINYINT(1) NOT NULL, ADD description LONGTEXT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sharable_user');
        $this->addSql('ALTER TABLE sharable DROP created_at, DROP begin_at, DROP end_at, DROP disabled, DROP description');
    }
}
