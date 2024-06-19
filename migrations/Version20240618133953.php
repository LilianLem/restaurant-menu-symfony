<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240618133953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_section ADD deleted_at DATETIME(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD deleted_at DATETIME(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE product_version ADD deleted_at DATETIME(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE restaurant_menu ADD deleted_at DATETIME(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE section ADD deleted_at DATETIME(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE section_product ADD deleted_at DATETIME(6) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_section DROP deleted_at');
        $this->addSql('ALTER TABLE product DROP deleted_at');
        $this->addSql('ALTER TABLE product_version DROP deleted_at');
        $this->addSql('ALTER TABLE restaurant_menu DROP deleted_at');
        $this->addSql('ALTER TABLE section DROP deleted_at');
        $this->addSql('ALTER TABLE section_product DROP deleted_at');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
