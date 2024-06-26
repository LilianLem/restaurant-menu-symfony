<?php

namespace App\Tests\Functional;

use App\Entity\Restaurant;
use Override;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

class RestaurantMenuResourceTest extends ApiTestCase
{
    use ResetDatabase, UserToMenuPopulateTrait;

    private Restaurant $restaurantToSoftDelete;

    #[Override] protected function setUp(): void
    {
        $this->populate();

        $this->menuA2->getMenuRestaurants()->first()->setVisible(false);
        $this->restaurantToSoftDelete = $this->restaurantB3;
    }

    public function testPostRestaurantMenu(): void
    {
        // As guest

        $this->browser()
            ->post("/restaurant_menus", [
                "json" => []
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $browser = $this->browser(actingAs: $this->userA)
            ->post("/restaurant_menus", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $browser->post("/restaurant_menus", [
                "json" => [
                    "restaurant" => "/restaurants/".$this->restaurantA2->getId()
                ]
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $browser->post("/restaurant_menus", [
                "json" => [
                    "menu" => "/menus/".$this->menuA2->getId()
                ]
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        // Linking a current "private" menu to a "public" restaurant, so that it can now be viewed by anyone
        $restaurantMenu = $browser->post("/restaurant_menus", [
                "json" => [
                    "restaurant" => "/restaurants/".$this->restaurantA1->getId(),
                    "menu" => "/menus/".$this->menuA2->getId(),
                    "visible" => true
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
                "restaurant",
                "menu",
                "visible",
                "rank",
                "createdAt",
                "updatedAt"
            ],
            array_keys($restaurantMenu),
            "RestaurantMenu keys are not matching when posting as normal user A"
        );

        $browser->get("/menus/".$this->menuA2->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('menuRestaurants[1].restaurant."@id"', "/restaurants/".$this->restaurantA1->getId())
        ;

        // As normal user B

        // Checking if normal user B can access newly public user A-owned menu
        $browser = $this->browser(actingAs: $this->userB)
            ->get("/menus/".$this->menuA2->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('menuRestaurants[1].restaurant."@id"', "/restaurants/".$this->restaurantA1->getId())
        ;

        // Linking an already "private" menu to a "private" restaurant (RestaurantMenu entity will be visible by default, but parent Restaurant has inTrash = true)
        $browser->post("/restaurant_menus", [
                "json" => [
                    "restaurant" => "/restaurants/".$this->restaurantB3->getId(),
                    "menu" => "/menus/".$this->menuB2->getId(),
                    "visible" => true
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
        ;

        $browser->get("/menus/".$this->menuB2->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('menuRestaurants[1].restaurant."@id"', "/restaurants/".$this->restaurantB3->getId())
        ;

        // Checking if normal user B can link an owned menu to a restaurant owned by user A, and if user B can link an owned restaurant to a menu owned by user A
        $browser->post("/restaurant_menus", [
                "json" => [
                    "restaurant" => "/restaurants/".$this->restaurantA1->getId(),
                    "menu" => "/menus/".$this->menuB1->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('detail', "restaurant: Ce restaurant ne vous appartient pas")
        ;

        $browser->post("/restaurant_menus", [
                "json" => [
                    "restaurant" => "/restaurants/".$this->restaurantB1->getId(),
                    "menu" => "/menus/".$this->menuA1->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('detail', "menu: Ce menu ne vous appartient pas")
        ;

        // Checking if normal user A can access just modified user B-owned menu linked to a new restaurant (but still private)
        $this->browser(actingAs: $this->userA)
            ->get("/menus/".$this->menuB2->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;

        // As admin, on behalf of normal user

        $browser = $this->browser(actingAs: $this->admin)->post("/restaurant_menus", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $restaurantMenu = $browser->post("/restaurant_menus", [
                "json" => [
                    "restaurant" => "/restaurants/".$this->restaurantB1->getId(),
                    "menu" => "/menus/".$this->menuB4->getId(),
                    "visible" => true
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
                "restaurant",
                "menu",
                "visible",
                "rank",
                "createdAt",
                "updatedAt"
            ],
            array_keys($restaurantMenu),
            "RestaurantMenu keys are not matching when posting as admin"
        );

        $browser->get("/menus/".$this->menuB4->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('menuRestaurants[2].restaurant."@id"', "/restaurants/".$this->restaurantB1->getId())
        ;
    }

    // TODO: simplify code (too cryptic because array indexes only rely on objects creation order)
    // TODO: add GET /menus/{id} requests to assert menus are either still visible with RestaurantMenu removed from menuRestaurants property, or not visible anymore
    public function testHardDeleteRestaurantMenu(): void
    {

        // As guest

        $this->browser()
            ->delete("/restaurant_menus/".$this->menuA3->getMenuRestaurants()->first()->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $restaurantMenuToDeleteId = $this->menuA3->getMenuRestaurants()->first()->getId();

        $this->browser(actingAs: $this->userA)
            ->delete("/restaurant_menus/".$this->menuB1->getMenuRestaurants()->first()->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/restaurant_menus/".$restaurantMenuToDeleteId)
            ->assertJson()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('detail', "id: Ce menu n'est relié qu'à un seul restaurant. Veuillez supprimer le menu directement.")

            ->delete("/menus/".$this->menuA3->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/restaurant_menus/".$restaurantMenuToDeleteId)
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;

        // As normal user B

        $restaurantMenuToDeleteId = $this->menuB4->getMenuRestaurants()->first()->getId();

        $browser = $this->browser(actingAs: $this->userB)
            ->delete("/restaurant_menus/".$this->menuA1->getMenuRestaurants()->first()->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/restaurant_menus/".$restaurantMenuToDeleteId)
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/restaurant_menus/".$restaurantMenuToDeleteId)
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;

        // Soft-deleting restaurant through API route for this test purposes
        $restaurantMenuToDeleteId = $this->restaurantToSoftDelete->getRestaurantMenus()->first()->getId();
        $browser->delete("/restaurants/".$this->restaurantToSoftDelete->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)
        ;

        $browser->delete("/restaurant_menus/".$restaurantMenuToDeleteId)
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;

        // As admin

        $restaurantMenuToDeleteId = $this->restaurantA1->getRestaurantMenus()->first()->getId();

        $this->browser(actingAs: $this->admin)
            ->delete("/restaurant_menus/".$restaurantMenuToDeleteId)
            ->assertJson()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('detail', "id: Ce menu n'est relié qu'à un seul restaurant. Veuillez supprimer le menu directement.")

            ->delete("/menus/".$this->menuA1->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/restaurant_menus/".$restaurantMenuToDeleteId)
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;
    }

    public function testPatchRestaurantMenu(): void
    {
        // TODO: implement test
    }
}