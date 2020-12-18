<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201218033806 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sharable ADD visible_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sharable ADD CONSTRAINT FK_83471A9C57EEBAA5 FOREIGN KEY (visible_by_id) REFERENCES user_class (id)');
        $this->addSql('CREATE INDEX IDX_83471A9C57EEBAA5 ON sharable (visible_by_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sharable DROP FOREIGN KEY FK_83471A9C57EEBAA5');
        $this->addSql('DROP INDEX IDX_83471A9C57EEBAA5 ON sharable');
        $this->addSql('ALTER TABLE sharable DROP visible_by_id');
    }
}
