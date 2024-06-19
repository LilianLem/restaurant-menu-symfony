<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240618120218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu ADD deleted_at DATETIME(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE restaurant ADD deleted_at DATETIME(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD deleted_at DATETIME(6) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu DROP deleted_at');
        $this->addSql('ALTER TABLE restaurant DROP deleted_at');
        $this->addSql('ALTER TABLE `user` DROP deleted_at');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
