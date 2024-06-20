<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230627174049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu ADD in_trash TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE restaurant ADD in_trash TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE user ADD enabled TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu DROP in_trash');
        $this->addSql('ALTER TABLE restaurant DROP in_trash');
        $this->addSql('ALTER TABLE `user` DROP enabled');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
