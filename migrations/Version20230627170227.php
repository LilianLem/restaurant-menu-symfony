<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230627170227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu CHANGE visible visible TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE menu_section ADD visible TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE product CHANGE price price INT UNSIGNED DEFAULT 0, CHANGE visible visible TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE product_version CHANGE visible visible TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE restaurant CHANGE visible visible TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE restaurant_menu CHANGE visible visible TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu CHANGE visible visible TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE menu_section DROP visible');
        $this->addSql('ALTER TABLE product CHANGE price price INT UNSIGNED DEFAULT NULL, CHANGE visible visible TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE product_version CHANGE visible visible TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE restaurant CHANGE visible visible TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE restaurant_menu CHANGE visible visible TINYINT(1) NOT NULL');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
