<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240617213207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu ADD slug VARCHAR(96) NOT NULL, CHANGE name name VARCHAR(64) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7D053A93989D9B62 ON menu (slug)');
        $this->addSql('ALTER TABLE restaurant ADD slug VARCHAR(96) NOT NULL, CHANGE name name VARCHAR(64) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB95123F989D9B62 ON restaurant (slug)');
        $this->addSql('ALTER TABLE section CHANGE name name VARCHAR(64) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_7D053A93989D9B62 ON menu');
        $this->addSql('ALTER TABLE menu DROP slug, CHANGE name name VARCHAR(128) NOT NULL');
        $this->addSql('DROP INDEX UNIQ_EB95123F989D9B62 ON restaurant');
        $this->addSql('ALTER TABLE restaurant DROP slug, CHANGE name name VARCHAR(128) NOT NULL');
        $this->addSql('ALTER TABLE section CHANGE name name VARCHAR(128) DEFAULT NULL');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
