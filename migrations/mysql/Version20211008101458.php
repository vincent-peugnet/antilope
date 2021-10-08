<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211008101458 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report_sharable_rule DROP FOREIGN KEY FK_DC09B0807BC501C7');
        $this->addSql('CREATE TABLE report (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, sharable_id INT NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, last_edited_at DATETIME NOT NULL, discr VARCHAR(255) NOT NULL, INDEX IDX_C42F7784A76ED395 (user_id), INDEX IDX_C42F7784CADDAACF (sharable_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE report_rule (report_id INT NOT NULL, rule_id INT NOT NULL, INDEX IDX_35F441374BD2A4C0 (report_id), INDEX IDX_35F44137744E0351 (rule_id), PRIMARY KEY(report_id, rule_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784CADDAACF FOREIGN KEY (sharable_id) REFERENCES sharable (id)');
        $this->addSql('ALTER TABLE report_rule ADD CONSTRAINT FK_35F441374BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report_rule ADD CONSTRAINT FK_35F44137744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE report_sharable');
        $this->addSql('DROP TABLE report_sharable_rule');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report_rule DROP FOREIGN KEY FK_35F441374BD2A4C0');
        $this->addSql('CREATE TABLE report_sharable (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, sharable_id INT NOT NULL, message LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, last_edited_at DATETIME NOT NULL, INDEX IDX_832783E1A76ED395 (user_id), INDEX IDX_832783E1CADDAACF (sharable_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE report_sharable_rule (report_sharable_id INT NOT NULL, rule_id INT NOT NULL, INDEX IDX_DC09B080744E0351 (rule_id), INDEX IDX_DC09B0807BC501C7 (report_sharable_id), PRIMARY KEY(report_sharable_id, rule_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE report_sharable ADD CONSTRAINT FK_832783E1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE report_sharable ADD CONSTRAINT FK_832783E1CADDAACF FOREIGN KEY (sharable_id) REFERENCES sharable (id)');
        $this->addSql('ALTER TABLE report_sharable_rule ADD CONSTRAINT FK_DC09B080744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE report_sharable_rule ADD CONSTRAINT FK_DC09B0807BC501C7 FOREIGN KEY (report_sharable_id) REFERENCES report_sharable (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE report');
        $this->addSql('DROP TABLE report_rule');
    }
}
