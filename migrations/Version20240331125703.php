<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240331125703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE refresh_tokens_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE allergen (id UUID NOT NULL, name VARCHAR(64) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_25BF08CE5E237E06 ON allergen (name)');
        $this->addSql('COMMENT ON COLUMN allergen.id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE menu (id UUID NOT NULL, name VARCHAR(128) NOT NULL, description TEXT DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, price INT DEFAULT NULL, in_trash BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN menu.id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE menu_section (id UUID NOT NULL, menu_id UUID NOT NULL, section_id UUID NOT NULL, visible BOOLEAN DEFAULT false NOT NULL, rank INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A5A86751CCD7E912 ON menu_section (menu_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A5A86751D823E37A ON menu_section (section_id)');
        $this->addSql('CREATE UNIQUE INDEX menu_section_unique ON menu_section (menu_id, section_id)');
        $this->addSql('CREATE UNIQUE INDEX menu_section_rank_unique ON menu_section (menu_id, rank)');
        $this->addSql('COMMENT ON COLUMN menu_section.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN menu_section.menu_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN menu_section.section_id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE product (id UUID NOT NULL, name VARCHAR(128) NOT NULL, description TEXT DEFAULT NULL, price INT DEFAULT 0, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN product.id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE product_allergen (product_id UUID NOT NULL, allergen_id UUID NOT NULL, PRIMARY KEY(product_id, allergen_id))');
        $this->addSql('CREATE INDEX IDX_EE0F62594584665A ON product_allergen (product_id)');
        $this->addSql('CREATE INDEX IDX_EE0F62596E775A4A ON product_allergen (allergen_id)');
        $this->addSql('COMMENT ON COLUMN product_allergen.product_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN product_allergen.allergen_id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE product_version (id UUID NOT NULL, product_id UUID NOT NULL, name VARCHAR(128) NOT NULL, price INT DEFAULT NULL, visible BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6EC5C8734584665A ON product_version (product_id)');
        $this->addSql('CREATE UNIQUE INDEX product_version_unique ON product_version (product_id, name)');
        $this->addSql('COMMENT ON COLUMN product_version.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN product_version.product_id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE refresh_tokens (id INT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E1C74F2195 ON refresh_tokens (refresh_token)');
        $this->addSql('CREATE TABLE restaurant (id UUID NOT NULL, owner_id UUID NOT NULL, name VARCHAR(128) NOT NULL, logo VARCHAR(255) DEFAULT NULL, visible BOOLEAN DEFAULT false NOT NULL, description TEXT DEFAULT NULL, in_trash BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EB95123F7E3C61F9 ON restaurant (owner_id)');
        $this->addSql('CREATE UNIQUE INDEX restaurant_owner_name_unique ON restaurant (name, owner_id)');
        $this->addSql('COMMENT ON COLUMN restaurant.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN restaurant.owner_id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE restaurant_menu (id UUID NOT NULL, restaurant_id UUID NOT NULL, menu_id UUID NOT NULL, visible BOOLEAN DEFAULT false NOT NULL, rank INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF13AAF7B1E7706E ON restaurant_menu (restaurant_id)');
        $this->addSql('CREATE INDEX IDX_BF13AAF7CCD7E912 ON restaurant_menu (menu_id)');
        $this->addSql('CREATE UNIQUE INDEX restaurant_menu_unique ON restaurant_menu (restaurant_id, menu_id)');
        $this->addSql('CREATE UNIQUE INDEX restaurant_menu_rank_unique ON restaurant_menu (restaurant_id, rank)');
        $this->addSql('COMMENT ON COLUMN restaurant_menu.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN restaurant_menu.restaurant_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN restaurant_menu.menu_id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE section (id UUID NOT NULL, name VARCHAR(128) DEFAULT NULL, price INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN section.id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE section_product (id UUID NOT NULL, section_id UUID NOT NULL, product_id UUID NOT NULL, visible BOOLEAN DEFAULT true NOT NULL, rank INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_10DC9A2D823E37A ON section_product (section_id)');
        $this->addSql('CREATE INDEX IDX_10DC9A24584665A ON section_product (product_id)');
        $this->addSql('CREATE UNIQUE INDEX section_product_unique ON section_product (section_id, product_id)');
        $this->addSql('CREATE UNIQUE INDEX section_product_rank_unique ON section_product (section_id, rank)');
        $this->addSql('COMMENT ON COLUMN section_product.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN section_product.section_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN section_product.product_id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, email VARCHAR(180) NOT NULL, roles JSONB NOT NULL, password VARCHAR(255) NOT NULL, enabled BOOLEAN DEFAULT true NOT NULL, verified BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE menu_section ADD CONSTRAINT FK_A5A86751CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE menu_section ADD CONSTRAINT FK_A5A86751D823E37A FOREIGN KEY (section_id) REFERENCES section (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_allergen ADD CONSTRAINT FK_EE0F62594584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_allergen ADD CONSTRAINT FK_EE0F62596E775A4A FOREIGN KEY (allergen_id) REFERENCES allergen (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_version ADD CONSTRAINT FK_6EC5C8734584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE restaurant ADD CONSTRAINT FK_EB95123F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE restaurant_menu ADD CONSTRAINT FK_BF13AAF7B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE restaurant_menu ADD CONSTRAINT FK_BF13AAF7CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE section_product ADD CONSTRAINT FK_10DC9A2D823E37A FOREIGN KEY (section_id) REFERENCES section (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE section_product ADD CONSTRAINT FK_10DC9A24584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE refresh_tokens_id_seq CASCADE');
        $this->addSql('ALTER TABLE menu_section DROP CONSTRAINT FK_A5A86751CCD7E912');
        $this->addSql('ALTER TABLE menu_section DROP CONSTRAINT FK_A5A86751D823E37A');
        $this->addSql('ALTER TABLE product_allergen DROP CONSTRAINT FK_EE0F62594584665A');
        $this->addSql('ALTER TABLE product_allergen DROP CONSTRAINT FK_EE0F62596E775A4A');
        $this->addSql('ALTER TABLE product_version DROP CONSTRAINT FK_6EC5C8734584665A');
        $this->addSql('ALTER TABLE restaurant DROP CONSTRAINT FK_EB95123F7E3C61F9');
        $this->addSql('ALTER TABLE restaurant_menu DROP CONSTRAINT FK_BF13AAF7B1E7706E');
        $this->addSql('ALTER TABLE restaurant_menu DROP CONSTRAINT FK_BF13AAF7CCD7E912');
        $this->addSql('ALTER TABLE section_product DROP CONSTRAINT FK_10DC9A2D823E37A');
        $this->addSql('ALTER TABLE section_product DROP CONSTRAINT FK_10DC9A24584665A');
        $this->addSql('DROP TABLE allergen');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE menu_section');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_allergen');
        $this->addSql('DROP TABLE product_version');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE restaurant');
        $this->addSql('DROP TABLE restaurant_menu');
        $this->addSql('DROP TABLE section');
        $this->addSql('DROP TABLE section_product');
        $this->addSql('DROP TABLE "user"');
    }
}
