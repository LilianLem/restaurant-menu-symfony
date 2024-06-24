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
use Override;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

class MenuResourceTest extends ApiTestCase
{
    use ResetDatabase;

    private User $userA;
    private User $userB;
    private User $userC;
    private User $admin;

    private Restaurant $restaurantA1;
    private Restaurant $restaurantA2;
    private Restaurant $restaurantA3;
    private Restaurant $restaurantB;
    private Restaurant $restaurantC;

    private Menu $menuA1;
    private Menu $menuA2;
    private Menu $menuA3;
    private Menu $menuA4;
    private Menu $menuB1;
    private Menu $menuB2;
    private Menu $menuC;

    #[Override] protected function setUp(): void
    {
        $this->populate();
    }

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

        $this->restaurantA3 = RestaurantFactory::createOne([
            "name" => "Restaurant A3",
            "visible" => false,
            "inTrash" => false,
            "owner" => $this->userA
        ]);

        $this->restaurantB = RestaurantFactory::createOne([
            "name" => "Restaurant B",
            "visible" => true,
            "inTrash" => false,
            "owner" => $this->userB
        ]);

        $this->restaurantC = RestaurantFactory::createOne([
            "name" => "Restaurant C",
            "visible" => true,
            "inTrash" => false,
            "owner" => $this->userC
        ]);

        // --- Menu ---

        $this->menuA1 = MenuFactory::createOne([
            "name" => "Menu A1",
            "deletedAt" => new Carbon("yesterday")
        ]);

        $this->menuA2 = MenuFactory::createOne([
            "name" => "Menu A2"
        ]);

        $this->menuA3 = MenuFactory::createOne([
            "name" => "Menu A3",
            "inTrash" => true
        ]);

        $this->menuA4 = MenuFactory::createOne([
            "name" => "Menu A4"
        ]);

        $this->menuB1 = MenuFactory::createOne([
            "name" => "Menu B1"
        ]);

        $this->menuB2 = MenuFactory::createOne([
            "name" => "Menu B2",
            "deletedAt" => new Carbon("yesterday")
        ]);

        $this->menuC = MenuFactory::createOne([
            "name" => "Menu C"
        ]);

        // --- RestaurantMenu ---

        $restaurantMenuData = [
            [$this->restaurantA1, $this->menuA1, true, 1],
            [$this->restaurantA1, $this->menuA2, true, 2],
            [$this->restaurantA2, $this->menuA2, true, 1],
            [$this->restaurantA2, $this->menuA3, false, 2],
            [$this->restaurantA3, $this->menuA4, true, 1],
            [$this->restaurantB, $this->menuB1, true, 1],
            [$this->restaurantB, $this->menuB2, true, 2],
            [$this->restaurantC, $this->menuC, true, 1]
        ];

        /** @var array{0: Restaurant, 1: Menu, 2: bool, 3: int} $data */
        foreach($restaurantMenuData as $data) {
            RestaurantMenuFactory::createOne([
                "restaurant" => $data[0],
                "menu" => $data[1],
                "visible" => $data[2],
                "rank" => $data[3]
            ]);
        }
    }

    public function testGetCollectionOfMenus(): void
    {
        // As guest

        $this->browser()
            ->get("/menus")
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $json = $this->browser(actingAs: $this->userA)
            ->get("/menus")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 3)
            ->json()->decoded()
        ;
        $menus = $json["hydra:member"];

        $this->assertSame(
            [
                "@id",
                "@type",
                "id",
                "name",
                "description",
                "slug",
                "menuSections",
                "menuRestaurants",
                //"icon",
                "price",
                "inTrash",
                "createdAt",
                "updatedAt"
            ],
            array_keys($menus[0]),
            "Menu keys are not matching when connected as normal user A"
        );

        $expectedMenuIds = array_map(fn(Menu $menu) => $menu->getId()->jsonSerialize(), [$this->menuA2, $this->menuA3, $this->menuA4]);
        $menuIds = array_map(fn(array $menu) => $menu["id"], $menus);
        $this->assertEquals($expectedMenuIds, $menuIds, "The menus in user A GET collection are not the ones expected");

        // As normal user B

        $json = $this->browser(actingAs: $this->userB)
            ->get("/menus")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 1)
            ->json()->decoded()
        ;
        $menus = $json["hydra:member"];

        $this->assertEquals($this->menuB1->getId()->jsonSerialize(), $menus[0]["id"], "The menu in user B GET collection is not the one expected");

        // As admin

        $json = $this->browser(actingAs: $this->admin)
            ->get("/menus")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 5)
            ->json()->decoded()
        ;
        $menus = $json["hydra:member"];

        $this->assertSame(
            [
                "@id",
                "@type",
                "id",
                "name",
                "description",
                "slug",
                "menuSections",
                "menuRestaurants",
                //"icon",
                "price",
                "inTrash",
                "createdAt",
                "updatedAt"
            ],
            array_keys($menus[0]),
            "Menu keys are not matching when connected as admin"
        );

        $expectedMenuIds = array_map(fn(Menu $menu) => $menu->getId()->jsonSerialize(), [$this->menuA2, $this->menuA3, $this->menuA4, $this->menuB1, $this->menuC]);
        $menuIds = array_map(fn(array $menu) => $menu["id"], $menus);
        $this->assertEquals($expectedMenuIds, $menuIds, "The menus in admin GET collection are not the ones expected");

        $this->browser(actingAs: $this->admin)
            ->get("/menus?menuRestaurants.restaurant.owner=/users/{$this->userA->getId()}")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 3)
        ;

        $this->browser(actingAs: $this->admin)
            ->get("/menus?menuRestaurants.restaurant.owner=/users/{$this->userB->getId()}")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 1)
        ;

        $json = $this->browser(actingAs: $this->admin)
            ->get("/menus?menuRestaurants.restaurant.owner=/users/{$this->userC->getId()}")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 1)
            ->json()->decoded()
        ;
        $menus = $json["hydra:member"];

        $this->assertEquals($this->menuC->getId()->jsonSerialize(), $menus[0]["id"], "The menu in admin GET collection (filtered by user C ownership) is not the one expected");
    }

    public function testGetMenu(): void
    {
        // As guest

        $expectedResultsData = [
            [$this->menuA2, Response::HTTP_OK],
            [$this->menuB1, Response::HTTP_OK],
            [$this->menuA1, Response::HTTP_NOT_FOUND],
            [$this->menuA3, Response::HTTP_UNAUTHORIZED],
            [$this->menuA4, Response::HTTP_UNAUTHORIZED],
            [$this->menuB2, Response::HTTP_NOT_FOUND],
            [$this->menuC, Response::HTTP_UNAUTHORIZED]
        ];

        $browser = $this->browser();

        $menu = null;
        /** @var array{0: Menu, 1: int} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/menus/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first menu
            $menu ??= $browser->json()->decoded();
        }

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "description",
                "slug",
                //"icon",
                "price",
                "menuSections",
                "menuRestaurants",
                "maxSectionRank"
            ],
            array_keys($menu),
            "Menu keys are not matching when requesting as guest"
        );

        // As normal user A

        $expectedResultsData = [
            [$this->menuA3, Response::HTTP_OK],
            [$this->menuA2, Response::HTTP_OK],
            [$this->menuA4, Response::HTTP_OK],
            [$this->menuB1, Response::HTTP_OK],
            [$this->menuA1, Response::HTTP_NOT_FOUND],
            [$this->menuB2, Response::HTTP_NOT_FOUND],
            [$this->menuC, Response::HTTP_FORBIDDEN]
        ];

        $browser = $this->browser(actingAs: $this->userA);

        $menu = null;
        /** @var array{0: Menu, 1: int} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/menus/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first menu
            $menu ??= $browser->json()->decoded();
        }

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "description",
                "slug",
                //"icon",
                "menuSections",
                "menuRestaurants",
                "price",
                "inTrash",
                "createdAt",
                "updatedAt",
                "maxSectionRank"
            ],
            array_keys($menu),
            "Menu keys are not matching when requesting as normal user A"
        );

        // As normal user B

        $expectedResultsData = [
            [$this->menuB1, Response::HTTP_OK],
            [$this->menuA2, Response::HTTP_OK],
            [$this->menuA1, Response::HTTP_NOT_FOUND],
            [$this->menuA3, Response::HTTP_FORBIDDEN],
            [$this->menuA4, Response::HTTP_FORBIDDEN],
            [$this->menuB2, Response::HTTP_NOT_FOUND],
            [$this->menuC, Response::HTTP_FORBIDDEN]
        ];

        $browser = $this->browser(actingAs: $this->userB);

        $menuB = null;
        $menuA = null;
        /** @var array{0: Menu, 1: int} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/menus/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first and second menu
            if(!$menuB) {
                $menuB = $browser->json()->decoded();
            } elseif(!$menuA) {
                $menuA = $browser->json()->decoded();
            }
        }

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "description",
                "slug",
                //"icon",
                "menuSections",
                "menuRestaurants",
                "price",
                "inTrash",
                "createdAt",
                "updatedAt",
                "maxSectionRank"
            ],
            array_keys($menuB),
            "Menu keys are not matching when requesting as normal user B (owner)"
        );

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "description",
                "slug",
                //"icon",
                "price",
                "menuSections",
                "menuRestaurants",
                "maxSectionRank"
            ],
            array_keys($menuA),
            "Menu keys are not matching when requesting as normal user B (not owner)"
        );

        // As admin

        $expectedResultsData = [
            [$this->menuA2, Response::HTTP_OK],
            [$this->menuA3, Response::HTTP_OK],
            [$this->menuA4, Response::HTTP_OK],
            [$this->menuB1, Response::HTTP_OK],
            [$this->menuC, Response::HTTP_OK],
            [$this->menuA1, Response::HTTP_NOT_FOUND],
            [$this->menuB2, Response::HTTP_NOT_FOUND]
        ];

        $browser = $this->browser(actingAs: $this->admin);

        $menu = null;
        /** @var array{0: Menu, 1: int} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/menus/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first menu
            $menu ??= $browser->json()->decoded();
        }

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "description",
                "slug",
                //"icon",
                "menuSections",
                "menuRestaurants",
                "price",
                "inTrash",
                "createdAt",
                "updatedAt",
                "maxSectionRank"
            ],
            array_keys($menu),
            "Menu keys are not matching when requesting as admin"
        );
    }

    public function testPostMenu(): void
    {
        // As guest

        $this->browser()
            ->post("/menus", [
                "json" => []
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $browser = $this->browser(actingAs: $this->userA)
            ->post("/menus", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $browser->post("/menus", [
                "json" => [
                    "name" => "My new menu"
                ]
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $menu = $browser->post("/menus", [
                "json" => [
                    "name" => "My new menu",
                    "firstRestaurant" => "/restaurants/".$this->restaurantA1->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
            ->json()->decoded()
        ;

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "description",
                "slug",
                //"icon",
                "menuSections",
                "menuRestaurants",
                "price",
                "inTrash",
                "createdAt",
                "updatedAt"
            ],
            array_keys($menu),
            "Menu keys are not matching when posting as normal user A"
        );

        $this->browser(actingAs: $this->admin)
            ->get($menu["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('menuRestaurants[0].restaurant."@id"', "/restaurants/".$this->restaurantA1->getId())
        ;

        // As normal user B

        // Checking if normal user B can access newly created user A-owned menu
        $browser = $this->browser(actingAs: $this->userB)
            ->get($menu["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;

        // Checking if normal user B can create a menu on behalf of user A
        $browser->post("/menus", [
                "json" => [
                    "name" => "My new menu",
                    "firstRestaurant" => "/restaurants/".$this->restaurantA1->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('detail', "firstRestaurant: Ce restaurant ne vous appartient pas")
        ;

        // As admin, on behalf of normal user

        $browser = $this->browser(actingAs: $this->admin)->post("/menus", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $menu = $browser->post("/menus", [
                "json" => [
                    "name" => "My new menu",
                    "firstRestaurant" => "/restaurants/".$this->restaurantA1->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
            ->json()->decoded()
        ;

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "description",
                "slug",
                //"icon",
                "menuSections",
                "menuRestaurants",
                "price",
                "inTrash",
                "createdAt",
                "updatedAt"
            ],
            array_keys($menu),
            "Menu keys are not matching when posting as admin"
        );

        $browser->get($menu["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('menuRestaurants[0].restaurant."@id"', "/restaurants/".$this->restaurantA1->getId())
        ;
    }

    public function testSoftDeleteMenu(): void
    {
        // As guest

        $this->browser()
            ->delete("/menus/".$this->menuA2->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $this->browser(actingAs: $this->userA)
            ->delete("/menus/".$this->menuB1->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/menus/".$this->menuA3->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/menus/".$this->menuA3->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)

            ->delete("/menus/".$this->menuA1->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;

        // As normal user B

        $this->browser(actingAs: $this->userB)
            ->delete("/menus/".$this->menuA2->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/menus/".$this->menuB1->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/menus/".$this->menuB1->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)

            ->delete("/menus/".$this->menuB2->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;

        // As admin

        $this->browser(actingAs: $this->admin)
            ->delete("/menus/".$this->menuA2->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/menus/".$this->menuA2->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;
    }

    public function testPatchMenu(): void
    {
        // TODO: implement test
    }
}