<?php

namespace App\Tests\Functional;

use App\Entity\Menu;
use App\Entity\Section;
use App\Factory\MenuSectionFactory;
use App\Factory\SectionFactory;
use Carbon\Carbon;

trait UserToSectionPopulateTrait
{
    use UserToMenuPopulateTrait {
        populate as userToMenuPopulate;
    }

    private Section $sectionA1;
    private Section $sectionA2;
    private Section $sectionA3;
    private Section $sectionA4;
    private Section $sectionB1;
    private Section $sectionB2;
    private Section $sectionB3;
    private Section $sectionB4;
    private Section $sectionC1;

    private function populate(): void
    {
        $this->userToMenuPopulate();

        // --- Section ---

        /** @var array<string, int> $sectionsData */
        $sectionsData = ["A" => 4, "B" => 4, "C" => 1];
        foreach($sectionsData as $letter => $count) {
            $i = 1;
            while($i <= $count) {
                $this->{"section".$letter.$i} = SectionFactory::createOne([
                    "name" => "Section ".$letter.$i
                ]);

                $i++;
            }
        }

        SectionFactory::find(["name" => "Section B3"])->setDeletedAt(new Carbon("yesterday"));

        // --- MenuSection ---

        $menuSectionData = [
            [$this->menuA1, $this->sectionA1, true, 1],
            [$this->menuA1, $this->sectionA2, true, 2],
            [$this->menuA2, $this->sectionA3, false, 2],
            [$this->menuA3, $this->sectionA4, true, 1],
            [$this->menuB1, $this->sectionB1, true, 1],
            [$this->menuB2, $this->sectionB2, true, 1],
            [$this->menuB3, $this->sectionB3, true, 1, true],
            [$this->menuB4, $this->sectionB4, true, 1],
            [$this->menuC1, $this->sectionC1, true, 1],
        ];

        /** @var array{0: Menu, 1: Section, 2: bool, 3: int, 4?: true} $data */
        foreach($menuSectionData as $data) {
            MenuSectionFactory::createOne([
                "menu" => $data[0],
                "section" => $data[1],
                "visible" => $data[2],
                "rank" => $data[3],
                "deletedAt" => isset($data[4]) ? new Carbon("yesterday") : null
            ]);
        }
    }
}