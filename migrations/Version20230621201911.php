<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230621201911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE allergen (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, UNIQUE INDEX UNIQ_25BF08CE5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE menu (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(128) NOT NULL, description LONGTEXT DEFAULT NULL, visible TINYINT(1) NOT NULL, icon VARCHAR(255) DEFAULT NULL, price INT UNSIGNED DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE menu_section (id INT UNSIGNED AUTO_INCREMENT NOT NULL, menu_id INT UNSIGNED NOT NULL, section_id INT UNSIGNED NOT NULL, rank INT UNSIGNED NOT NULL, INDEX IDX_A5A86751CCD7E912 (menu_id), UNIQUE INDEX UNIQ_A5A86751D823E37A (section_id), UNIQUE INDEX menu_section_unique (menu_id, section_id), UNIQUE INDEX menu_section_rank_unique (menu_id, rank), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(128) NOT NULL, description LONGTEXT DEFAULT NULL, price INT UNSIGNED DEFAULT NULL, visible TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_allergen (product_id INT UNSIGNED NOT NULL, allergen_id INT UNSIGNED NOT NULL, INDEX IDX_EE0F62594584665A (product_id), INDEX IDX_EE0F62596E775A4A (allergen_id), PRIMARY KEY(product_id, allergen_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_version (id INT UNSIGNED AUTO_INCREMENT NOT NULL, product_id INT UNSIGNED NOT NULL, name VARCHAR(128) NOT NULL, price INT UNSIGNED DEFAULT NULL, visible TINYINT(1) NOT NULL, INDEX IDX_6EC5C8734584665A (product_id), UNIQUE INDEX product_version_unique (product_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restaurant (id INT UNSIGNED AUTO_INCREMENT NOT NULL, owner_id INT UNSIGNED NOT NULL, name VARCHAR(128) NOT NULL, logo VARCHAR(255) DEFAULT NULL, visible TINYINT(1) NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_EB95123F7E3C61F9 (owner_id), UNIQUE INDEX restaurant_owner_name_unique (name, owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restaurant_menu (id INT UNSIGNED AUTO_INCREMENT NOT NULL, restaurant_id INT UNSIGNED NOT NULL, menu_id INT UNSIGNED NOT NULL, visible TINYINT(1) NOT NULL, rank INT UNSIGNED NOT NULL, INDEX IDX_BF13AAF7B1E7706E (restaurant_id), INDEX IDX_BF13AAF7CCD7E912 (menu_id), UNIQUE INDEX restaurant_menu_unique (restaurant_id, menu_id), UNIQUE INDEX restaurant_menu_rank_unique (restaurant_id, rank), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE section (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(128) DEFAULT NULL, price INT UNSIGNED DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE section_product (id INT UNSIGNED AUTO_INCREMENT NOT NULL, section_id INT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL, rank INT UNSIGNED NOT NULL, INDEX IDX_10DC9A2D823E37A (section_id), INDEX IDX_10DC9A24584665A (product_id), UNIQUE INDEX section_product_unique (section_id, product_id), UNIQUE INDEX section_product_rank_unique (section_id, rank), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT UNSIGNED AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE menu_section ADD CONSTRAINT FK_A5A86751CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE menu_section ADD CONSTRAINT FK_A5A86751D823E37A FOREIGN KEY (section_id) REFERENCES section (id)');
        $this->addSql('ALTER TABLE product_allergen ADD CONSTRAINT FK_EE0F62594584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_allergen ADD CONSTRAINT FK_EE0F62596E775A4A FOREIGN KEY (allergen_id) REFERENCES allergen (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_version ADD CONSTRAINT FK_6EC5C8734584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE restaurant ADD CONSTRAINT FK_EB95123F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE restaurant_menu ADD CONSTRAINT FK_BF13AAF7B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE restaurant_menu ADD CONSTRAINT FK_BF13AAF7CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE section_product ADD CONSTRAINT FK_10DC9A2D823E37A FOREIGN KEY (section_id) REFERENCES section (id)');
        $this->addSql('ALTER TABLE section_product ADD CONSTRAINT FK_10DC9A24584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_section DROP FOREIGN KEY FK_A5A86751CCD7E912');
        $this->addSql('ALTER TABLE menu_section DROP FOREIGN KEY FK_A5A86751D823E37A');
        $this->addSql('ALTER TABLE product_allergen DROP FOREIGN KEY FK_EE0F62594584665A');
        $this->addSql('ALTER TABLE product_allergen DROP FOREIGN KEY FK_EE0F62596E775A4A');
        $this->addSql('ALTER TABLE product_version DROP FOREIGN KEY FK_6EC5C8734584665A');
        $this->addSql('ALTER TABLE restaurant DROP FOREIGN KEY FK_EB95123F7E3C61F9');
        $this->addSql('ALTER TABLE restaurant_menu DROP FOREIGN KEY FK_BF13AAF7B1E7706E');
        $this->addSql('ALTER TABLE restaurant_menu DROP FOREIGN KEY FK_BF13AAF7CCD7E912');
        $this->addSql('ALTER TABLE section_product DROP FOREIGN KEY FK_10DC9A2D823E37A');
        $this->addSql('ALTER TABLE section_product DROP FOREIGN KEY FK_10DC9A24584665A');
        $this->addSql('DROP TABLE allergen');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE menu_section');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_allergen');
        $this->addSql('DROP TABLE product_version');
        $this->addSql('DROP TABLE restaurant');
        $this->addSql('DROP TABLE restaurant_menu');
        $this->addSql('DROP TABLE section');
        $this->addSql('DROP TABLE section_product');
        $this->addSql('DROP TABLE `user`');
    }
}
