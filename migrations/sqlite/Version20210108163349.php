<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210108163349 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE interested (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, sharable_id INTEGER NOT NULL, created_at DATETIME NOT NULL, reviewed BOOLEAN NOT NULL)');
        $this->addSql('CREATE INDEX IDX_C4524078A76ED395 ON interested (user_id)');
        $this->addSql('CREATE INDEX IDX_C4524078CADDAACF ON interested (sharable_id)');
        $this->addSql('CREATE TABLE invitation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent_id INTEGER NOT NULL, child_id INTEGER DEFAULT NULL, created_at DATETIME NOT NULL, code VARCHAR(64) NOT NULL)');
        $this->addSql('CREATE INDEX IDX_F11D61A2727ACA70 ON invitation (parent_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F11D61A2DD62C21B ON invitation (child_id)');
        $this->addSql('CREATE TABLE manage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, sharable_id INTEGER NOT NULL, created_at DATETIME NOT NULL, contactable BOOLEAN NOT NULL)');
        $this->addSql('CREATE INDEX IDX_2472AA4AA76ED395 ON manage (user_id)');
        $this->addSql('CREATE INDEX IDX_2472AA4ACADDAACF ON manage (sharable_id)');
        $this->addSql('CREATE TABLE sharable (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, visible_by_id INTEGER DEFAULT NULL, name VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL, begin_at DATETIME DEFAULT NULL, end_at DATETIME DEFAULT NULL, disabled BOOLEAN NOT NULL, description VARCHAR(255) NOT NULL, details CLOB NOT NULL, responsibility BOOLEAN NOT NULL, last_edited_at DATETIME NOT NULL, interested_method SMALLINT NOT NULL)');
        $this->addSql('CREATE INDEX IDX_83471A9C57EEBAA5 ON sharable (visible_by_id)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_class_id INTEGER NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, description CLOB DEFAULT NULL, share_score INTEGER NOT NULL, paranoia SMALLINT NOT NULL, email VARCHAR(180) NOT NULL, is_verified BOOLEAN NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE INDEX IDX_8D93D649A8DEF10 ON user (user_class_id)');
        $this->addSql('CREATE TABLE user_class (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, rank SMALLINT NOT NULL, name VARCHAR(32) NOT NULL, share BOOLEAN NOT NULL, access BOOLEAN NOT NULL, can_invite BOOLEAN NOT NULL, max_paranoia SMALLINT NOT NULL, invite_frequency SMALLINT NOT NULL, share_score_req INTEGER NOT NULL, account_age_req INTEGER NOT NULL, validated_req INTEGER NOT NULL, verified_req BOOLEAN NOT NULL)');
        $this->addSql('CREATE TABLE user_contact (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, created_at DATETIME NOT NULL, type VARCHAR(64) NOT NULL, content VARCHAR(255) NOT NULL, info CLOB DEFAULT NULL, disabled BOOLEAN NOT NULL)');
        $this->addSql('CREATE INDEX IDX_146FF832A76ED395 ON user_contact (user_id)');
        $this->addSql('CREATE TABLE validation (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, sharable_id INTEGER NOT NULL, user_id INTEGER NOT NULL, send_at DATETIME NOT NULL, message VARCHAR(1024) DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_16AC5B6ECADDAACF ON validation (sharable_id)');
        $this->addSql('CREATE INDEX IDX_16AC5B6EA76ED395 ON validation (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE interested');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('DROP TABLE manage');
        $this->addSql('DROP TABLE sharable');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_class');
        $this->addSql('DROP TABLE user_contact');
        $this->addSql('DROP TABLE validation');
    }
}
