<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210122203139 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_class DROP FOREIGN KEY FK_F89E2C7AA23F6C8');
        $this->addSql('ALTER TABLE user_class ADD CONSTRAINT FK_F89E2C7AA23F6C8 FOREIGN KEY (next_id) REFERENCES user_class (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_class DROP FOREIGN KEY FK_F89E2C7AA23F6C8');
        $this->addSql('ALTER TABLE user_class ADD CONSTRAINT FK_F89E2C7AA23F6C8 FOREIGN KEY (next_id) REFERENCES user_class (id)');
    }
}
