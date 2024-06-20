<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240319164411 extends AbstractMigration
{
    const array MIGRATION_DATA = [
        31 => "debd76e3d2f6cbe916eef4b20",
        32 => "e02678b45fed0c2d47584462c",
        33 => "e169337690f30157e2e4f5a75",
        34 => "e272ce9cb024afa5fd1a7610e",
        35 => "e372c2df35069374658eab19f",
        36 => "e466fa99a81681977981927cb",
        37 => "e5bd2d42f1f69ebfccb6a8435",
        38 => "e6ae60eb508bc5d4ec69ffe19",
        39 => "e8741cee97e99a97145b1cded",
        3130 => "e9bf6a0bb4122502f904d5f5c",
        3131 => "eb1214dec5fe21a1040a91cbb",
        3132 => "ecd0ca151792d8872e4af29c8",
        3133 => "edc625cd1367698f5960d0e79",
        3134 => "ee9f33645a931a8ff28ac52b9"
    ];

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        foreach(self::MIGRATION_DATA as $id => $ulid) {
            $this->addSql('UPDATE allergen SET `id` = '.$this->getFullHexbinUlidFromShortenedUlid($ulid).' WHERE `id` = '.$this->getFullHexbinIdFromShortenedId($id));
        }
    }

    public function down(Schema $schema): void
    {
        foreach(self::MIGRATION_DATA as $id => $ulid) {
            $this->addSql('UPDATE allergen SET `id` = '.$this->getFullHexbinIdFromShortenedId($id).' WHERE `id` = '.$this->getFullHexbinUlidFromShortenedUlid($ulid));
        }
    }

    private function getFullHexbinIdFromShortenedId(int $id): string {
        return "0x".str_pad(strval($id), 4, "0").str_repeat("0", 28);
    }

    private function getFullHexbinUlidFromShortenedUlid(string $ulid): string {
        return "0x018e575".$ulid;
    }
}
