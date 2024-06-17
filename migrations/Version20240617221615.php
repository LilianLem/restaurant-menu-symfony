<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240617221615 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE menu ADD created_at DATETIME(6) DEFAULT NULL, ADD updated_at DATETIME(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE menu_section ADD created_at DATETIME(6) DEFAULT NULL, ADD updated_at DATETIME(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD created_at DATETIME(6) DEFAULT NULL, ADD updated_at DATETIME(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE product_version ADD created_at DATETIME(6) DEFAULT NULL, ADD updated_at DATETIME(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE restaurant ADD created_at DATETIME(6) DEFAULT NULL, ADD updated_at DATETIME(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE restaurant_menu ADD created_at DATETIME(6) DEFAULT NULL, ADD updated_at DATETIME(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE section ADD created_at DATETIME(6) DEFAULT NULL, ADD updated_at DATETIME(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE section_product ADD created_at DATETIME(6) DEFAULT NULL, ADD updated_at DATETIME(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD created_at DATETIME(6) DEFAULT NULL, ADD updated_at DATETIME(6) DEFAULT NULL');

        $this->addSql('UPDATE menu SET created_at = NOW(), updated_at = NOW()');
        $this->addSql('UPDATE menu_section SET created_at = NOW(), updated_at = NOW()');
        $this->addSql('UPDATE product SET created_at = NOW(), updated_at = NOW()');
        $this->addSql('UPDATE product_version SET created_at = NOW(), updated_at = NOW()');
        $this->addSql('UPDATE restaurant SET created_at = NOW(), updated_at = NOW()');
        $this->addSql('UPDATE restaurant_menu SET created_at = NOW(), updated_at = NOW()');
        $this->addSql('UPDATE section SET created_at = NOW(), updated_at = NOW()');
        $this->addSql('UPDATE section_product SET created_at = NOW(), updated_at = NOW()');
        $this->addSql('UPDATE user SET created_at = NOW(), updated_at = NOW()');

        $this->addSql('ALTER TABLE menu CHANGE created_at created_at DATETIME(6) NOT NULL, CHANGE updated_at updated_at DATETIME(6) NOT NULL');
        $this->addSql('ALTER TABLE menu_section CHANGE created_at created_at DATETIME(6) NOT NULL, CHANGE updated_at updated_at DATETIME(6) NOT NULL');
        $this->addSql('ALTER TABLE product CHANGE created_at created_at DATETIME(6) NOT NULL, CHANGE updated_at updated_at DATETIME(6) NOT NULL');
        $this->addSql('ALTER TABLE product_version CHANGE created_at created_at DATETIME(6) NOT NULL, CHANGE updated_at updated_at DATETIME(6) NOT NULL');
        $this->addSql('ALTER TABLE restaurant CHANGE created_at created_at DATETIME(6) NOT NULL, CHANGE updated_at updated_at DATETIME(6) NOT NULL');
        $this->addSql('ALTER TABLE restaurant_menu CHANGE created_at created_at DATETIME(6) NOT NULL, CHANGE updated_at updated_at DATETIME(6) NOT NULL');
        $this->addSql('ALTER TABLE section CHANGE created_at created_at DATETIME(6) NOT NULL, CHANGE updated_at updated_at DATETIME(6) NOT NULL');
        $this->addSql('ALTER TABLE section_product CHANGE created_at created_at DATETIME(6) NOT NULL, CHANGE updated_at updated_at DATETIME(6) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME(6) NOT NULL, CHANGE updated_at updated_at DATETIME(6) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE menu_section DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE product DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE product_version DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE restaurant DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE restaurant_menu DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE section DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE section_product DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE `user` DROP created_at, DROP updated_at');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
