<?php

namespace App\Tests\Functional;

use App\Entity\Menu;
use App\Entity\Restaurant;
use App\Entity\Section;
use App\Entity\User;
use App\Factory\MenuFactory;
use App\Factory\MenuSectionFactory;
use App\Factory\RestaurantFactory;
use App\Factory\RestaurantMenuFactory;
use App\Factory\SectionFactory;
use App\Factory\UserFactory;
use Carbon\Carbon;

trait UserToSectionPopulateTrait
{
    private User $userA;
    private User $userB;
    private User $userC;
    private User $admin;

    private Restaurant $restaurantA1;
    private Restaurant $restaurantA2;
    private Restaurant $restaurantB1;
    private Restaurant $restaurantB2;
    private Restaurant $restaurantB3;
    private Restaurant $restaurantB4;
    private Restaurant $restaurantC1;

    private Menu $menuA1;
    private Menu $menuA2;
    private Menu $menuA3;
    private Menu $menuB1;
    private Menu $menuB2;
    private Menu $menuB3;
    private Menu $menuB4;
    private Menu $menuC1;

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
        $this->userA = UserFactory::createOne(["verified" => true]);
        $this->userB = UserFactory::createOne(["verified" => true]);
        $this->userC = UserFactory::createOne([
            "enabled" => false,
            "verified" => true
        ]);

        $this->admin = UserFactory::new()->asAdmin()->create();

        // --- Restaurant ---

        $this->restaurantA1 = RestaurantFactory::createOne([
            "name" => "Restaurant A1",
            "visible" => true,
            "inTrash" => false,
            "owner" => $this->userA
        ]);

        $this->restaurantA2 = RestaurantFactory::createOne([
            "name" => "Restaurant A2",
            "visible" => true,
            "inTrash" => false,
            "owner" => $this->userA
        ]);

        $this->restaurantB1 = RestaurantFactory::createOne([
            "name" => "Restaurant B1",
            "visible" => true,
            "inTrash" => false,
            "owner" => $this->userB
        ]);

        $this->restaurantB2 = RestaurantFactory::createOne([
            "name" => "Restaurant B2",
            "visible" => false,
            "inTrash" => false,
            "owner" => $this->userB
        ]);

        $this->restaurantB3 = RestaurantFactory::createOne([
            "name" => "Restaurant B3",
            "visible" => true,
            "inTrash" => true,
            "owner" => $this->userB
        ]);

        $this->restaurantB4 = RestaurantFactory::createOne([
            "name" => "Restaurant B4",
            "visible" => true,
            "inTrash" => false,
            "deletedAt" => new Carbon("yesterday"),
            "owner" => $this->userB
        ]);

        $this->restaurantC1 = RestaurantFactory::createOne([
            "name" => "Restaurant C1",
            "visible" => true,
            "inTrash" => false,
            "owner" => $this->userC
        ]);

        // --- Menu ---

        $this->menuA1 = MenuFactory::createOne([
            "name" => "Menu A1"
        ]);

        $this->menuA2 = MenuFactory::createOne([
            "name" => "Menu A2"
        ]);

        $this->menuA3 = MenuFactory::createOne([
            "name" => "Menu A3",
            "inTrash" => true
        ]);

        $this->menuB1 = MenuFactory::createOne([
            "name" => "Menu B1"
        ]);

        $this->menuB2 = MenuFactory::createOne([
            "name" => "Menu B2"
        ]);

        $this->menuB3 = MenuFactory::createOne([
            "name" => "Menu B3",
            "deletedAt" => new Carbon("yesterday")
        ]);

        $this->menuB4 = MenuFactory::createOne([
            "name" => "Menu B4"
        ]);

        $this->menuC1 = MenuFactory::createOne([
            "name" => "Menu C1"
        ]);

        // --- RestaurantMenu ---

        $restaurantMenuData = [
            [$this->restaurantA1, $this->menuA1, true, 1],
            [$this->restaurantA2, $this->menuA2, true, 1],
            [$this->restaurantA2, $this->menuA3, true, 2],
            [$this->restaurantB1, $this->menuB1, true, 1],
            [$this->restaurantB1, $this->menuB2, false, 2],
            [$this->restaurantB2, $this->menuB3, true, 1],
            [$this->restaurantB2, $this->menuB4, true, 2],
            [$this->restaurantB3, $this->menuB4, true, 1],
            [$this->restaurantB4, $this->menuB4, true, 1, true],
            [$this->restaurantC1, $this->menuC1, true, 1],
        ];

        /** @var array{0: Restaurant, 1: Menu, 2: bool, 3: int, 4?: true} $data */
        foreach($restaurantMenuData as $data) {
            RestaurantMenuFactory::createOne([
                "restaurant" => $data[0],
                "menu" => $data[1],
                "visible" => $data[2],
                "rank" => $data[3],
                "deletedAt" => isset($data[4]) ? new Carbon("yesterday") : null
            ]);
        }

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