<?php

namespace App\Tests\Functional;

use App\Entity\Menu;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Factory\MenuFactory;
use App\Factory\RestaurantFactory;
use App\Factory\RestaurantMenuFactory;
use App\Factory\UserFactory;
use Carbon\Carbon;

trait UserToMenuPopulateTrait
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
    }
}