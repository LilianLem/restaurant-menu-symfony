<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230622113214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO `allergen` (`id`,`name`) VALUES (1,"Arachides"),(2,"Céleri"),(3,"Céréales"),(4,"Crustacés"),(5,"Fruits à coque"),(6,"Graines de sésame"),(7,"Lait"),(8,"Lupin"),(9,"Mollusques"),(10,"Moutarde"),(11,"Œufs"),(12,"Poissons"),(13,"Soja"),(14,"Sulfites")');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM allergen WHERE id < 15');
    }
}
