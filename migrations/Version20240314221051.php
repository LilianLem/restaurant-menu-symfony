<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240314221051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
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

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE allergen CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE menu CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE menu_section CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE menu_id menu_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE section_id section_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE product CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE product_allergen CHANGE product_id product_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE allergen_id allergen_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE product_version CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE product_id product_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE restaurant CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE owner_id owner_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE restaurant_menu CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE restaurant_id restaurant_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE menu_id menu_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE section CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE section_product CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE section_id section_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE product_id product_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE user CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');

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

        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE allergen CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE menu CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE menu_section CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE menu_id menu_id INT UNSIGNED NOT NULL, CHANGE section_id section_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE product CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE product_allergen CHANGE product_id product_id INT UNSIGNED NOT NULL, CHANGE allergen_id allergen_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE product_version CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE product_id product_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE restaurant CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE owner_id owner_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE restaurant_menu CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE restaurant_id restaurant_id INT UNSIGNED NOT NULL, CHANGE menu_id menu_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE section CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE section_product CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE section_id section_id INT UNSIGNED NOT NULL, CHANGE product_id product_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');

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
}
