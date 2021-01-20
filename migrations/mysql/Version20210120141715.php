<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210120141715 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_class ADD next_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_class ADD CONSTRAINT FK_F89E2C7AA23F6C8 FOREIGN KEY (next_id) REFERENCES user_class (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F89E2C78879E8E5 ON user_class (rank)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F89E2C7AA23F6C8 ON user_class (next_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_class DROP FOREIGN KEY FK_F89E2C7AA23F6C8');
        $this->addSql('DROP INDEX UNIQ_F89E2C78879E8E5 ON user_class');
        $this->addSql('DROP INDEX UNIQ_F89E2C7AA23F6C8 ON user_class');
        $this->addSql('ALTER TABLE user_class DROP next_id');
    }
}
