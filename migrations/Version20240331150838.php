<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240331150838 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX menu_section_rank_unique');
        $this->addSql('DROP INDEX restaurant_menu_rank_unique');
        $this->addSql('DROP INDEX section_product_rank_unique');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX menu_section_rank_unique ON menu_section (menu_id, rank)');
        $this->addSql('CREATE UNIQUE INDEX section_product_rank_unique ON section_product (section_id, rank)');
        $this->addSql('CREATE UNIQUE INDEX restaurant_menu_rank_unique ON restaurant_menu (restaurant_id, rank)');
    }
}
