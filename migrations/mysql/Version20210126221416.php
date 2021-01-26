<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210126221416 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_F89E2C78879E8E5 ON user_class');
        $this->addSql('ALTER TABLE user_class DROP rank');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_class ADD rank SMALLINT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F89E2C78879E8E5 ON user_class (rank)');
    }
}
