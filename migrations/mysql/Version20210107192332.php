<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210107192332 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE interested (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, sharable_id INT NOT NULL, created_at DATETIME NOT NULL, reviewed TINYINT(1) NOT NULL, INDEX IDX_C4524078A76ED395 (user_id), INDEX IDX_C4524078CADDAACF (sharable_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE interested ADD CONSTRAINT FK_C4524078A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE interested ADD CONSTRAINT FK_C4524078CADDAACF FOREIGN KEY (sharable_id) REFERENCES sharable (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE interested');
    }
}
