<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210227231027 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sharable_tag (sharable_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_634CDD9BCADDAACF (sharable_id), INDEX IDX_634CDD9BBAD26311 (tag_id), PRIMARY KEY(sharable_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sharable_tag ADD CONSTRAINT FK_634CDD9BCADDAACF FOREIGN KEY (sharable_id) REFERENCES sharable (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sharable_tag ADD CONSTRAINT FK_634CDD9BBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE sharable_tag');
    }
}
