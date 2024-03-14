<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240313201535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP visible');
        $this->addSql('ALTER TABLE refresh_tokens RENAME INDEX uniq_c74f2195c74f2195 TO UNIQ_9BACE7E1C74F2195');
        $this->addSql('ALTER TABLE section_product ADD visible TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE user ADD verified TINYINT(1) DEFAULT 0 NOT NULL, CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD visible TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE refresh_tokens RENAME INDEX uniq_9bace7e1c74f2195 TO UNIQ_C74F2195C74F2195');
        $this->addSql('ALTER TABLE section_product DROP visible');
        $this->addSql('ALTER TABLE `user` DROP verified, CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }
}
