<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210107000841 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE manage (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, sharable_id INT NOT NULL, created_at DATETIME NOT NULL, contactable TINYINT(1) NOT NULL, INDEX IDX_2472AA4AA76ED395 (user_id), INDEX IDX_2472AA4ACADDAACF (sharable_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE manage ADD CONSTRAINT FK_2472AA4AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE manage ADD CONSTRAINT FK_2472AA4ACADDAACF FOREIGN KEY (sharable_id) REFERENCES sharable (id)');
        $this->addSql('DROP TABLE sharable_user');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sharable_user (sharable_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_9E9F8B75CADDAACF (sharable_id), INDEX IDX_9E9F8B75A76ED395 (user_id), PRIMARY KEY(sharable_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sharable_user ADD CONSTRAINT FK_9E9F8B75A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sharable_user ADD CONSTRAINT FK_9E9F8B75CADDAACF FOREIGN KEY (sharable_id) REFERENCES sharable (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE manage');
    }
}
