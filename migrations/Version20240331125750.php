<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240331125750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO allergen (id,name) VALUES
	 ('018e947e-6acd-7b4a-2863-604e62ab5a9d','Arachides'),
	 ('018e9480-8dff-c1ac-3859-f6935fdf7c8a','Céleri'),
	 ('018e9480-9744-fbb6-9209-95bf5fcd7d4f','Céréales'),
	 ('018e9480-a8be-a973-94ca-5d1beccd2104','Crustacés'),
	 ('018e9480-ba00-34e0-dc5f-4e8719f3db45','Fruits à coque'),
	 ('018e9480-d0b4-4991-208f-95fbb5d555c5','Graines de sésame'),
	 ('018e9480-e79c-03c5-2d9b-980c7bceb880','Lait'),
	 ('018e9480-f28d-6d79-da8f-a4cfffa483ed','Lupin'),
	 ('018e9481-01e9-c162-2b85-246180b11298','Mollusques'),
	 ('018e9481-0a8d-c9dd-ddd1-694aa8ee2665','Moutarde'),
	 ('018e9481-20f0-cb24-d9ab-7d5aecddaff7','Œufs'),
	 ('018e9481-3b90-8468-39be-994f456c48ec','Poissons'),
	 ('018e9481-43a1-7f22-7b42-525a74286263','Soja'),
	 ('018e9481-4dd4-6349-4b64-e0ccf3846b3f','Sulfites');
");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM allergen WHERE id LIKE '018e947e%' OR id LIKE '018e948%'");
    }
}
