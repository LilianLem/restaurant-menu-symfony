<?php

namespace App\Tests\Functional;

use App\Factory\RestaurantFactory;
use App\Factory\UserFactory;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

class RestaurantResourceTest extends ApiTestCase
{
    use ResetDatabase;

    public function testGetCollectionOfRestaurants(): void
    {
        RestaurantFactory::createMany(8, ["owner" => UserFactory::createOne()]);
        $user = UserFactory::createOne();
        RestaurantFactory::createMany(2, ["owner" => $user]);

        $admin = UserFactory::new()->asAdmin()->create();

        // As guest

        $this->browser()
            ->get("/api/restaurants")
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user

        $json = $this->browser(actingAs: $user)
            ->get("/api/restaurants")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 2)
            ->json()->decoded()
        ;
        $restaurants = $json["hydra:member"];

        $this->assertSame(
            array_keys($restaurants[0]),
            [
                "@id",
                "@type",
                "id",
                "name",
                "logo",
                "visible",
                "description",
                "restaurantMenus",
                "owner",
                "inTrash"
            ],
            "Restaurant keys are not matching when connected as normal user"
        );

        $this->assertSame(
            array_count_values(array_map(fn(array $restaurant) => $restaurant["owner"], $restaurants)),
            ["/api/users/".$user->getId() => 2],
            "Some restaurants retrieved when connected as normal user are not self-owned"
        );

        // As admin

        $json = $this->browser(actingAs: $admin)
            ->get("/api/restaurants")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 10)
            ->json()->decoded()
        ;
        $restaurants = $json["hydra:member"];

        $this->assertSame(
            array_keys($restaurants[0]),
            [
                "@id",
                "@type",
                "id",
                "name",
                "logo",
                "visible",
                "description",
                "restaurantMenus",
                "owner",
                "inTrash"
            ],
            "Restaurant keys are not matching when connected as admin"
        );
    }

    public function testGetRestaurant(): void
    {
        $user = UserFactory::createOne(["verified" => true]);
        $restaurantA = RestaurantFactory::createOne([
            "owner" => $user,
            "visible" => true,
            "inTrash" => false
        ]);
        $restaurantB = RestaurantFactory::createOne([
            "owner" => $user,
            "visible" => false,
            "inTrash" => false
        ]);
        $restaurantC = RestaurantFactory::createOne([
            "owner" => $user,
            "visible" => true,
            "inTrash" => true
        ]);
        $user2 = UserFactory::createOne(["verified" => true]);

        // As guest

        $restaurant = $this->browser()
            ->get("/api/restaurants/".$restaurantB->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->get("/api/restaurants/".$restaurantC->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->get("/api/restaurants/".$restaurantA->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json()->decoded()
        ;

        $this->assertSame(
            array_keys($restaurant),
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "logo",
                "visible",
                "description",
                "restaurantMenus",
                "inTrash"
            ],
            "Restaurant keys are not matching when requesting as guest"
        );

        // As normal user (owner)

        $restaurant = $this->browser(actingAs: $user)
            ->get("/api/restaurants/".$restaurantB->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->get("/api/restaurants/".$restaurantC->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->get("/api/restaurants/".$restaurantA->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json()->decoded()
        ;

        $this->assertSame(
            array_keys($restaurant),
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "logo",
                "visible",
                "description",
                "restaurantMenus",
                "owner",
                "inTrash",
                "maxMenuRank"
            ],
            "Restaurant keys are not matching when connected as normal user (owner)"
        );

        // As normal user (not owner)

        $restaurant = $this->browser(actingAs: $user2)
            ->get("/api/restaurants/".$restaurantB->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->get("/api/restaurants/".$restaurantC->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->get("/api/restaurants/".$restaurantA->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json()->decoded()
        ;

        $this->assertSame(
            array_keys($restaurant),
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "logo",
                "visible",
                "description",
                "restaurantMenus",
                "inTrash"
            ],
            "Restaurant keys are not matching when requesting as normal user (not owner)"
        );

        // As admin

        $admin = UserFactory::new()->asAdmin()->create();

        $restaurant = $this->browser(actingAs: $admin)
            ->get("/api/restaurants/".$restaurantB->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->get("/api/restaurants/".$restaurantC->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->get("/api/restaurants/".$restaurantA->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json()->decoded()
        ;

        $this->assertSame(
            array_keys($restaurant),
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "logo",
                "visible",
                "description",
                "restaurantMenus",
                "owner",
                "inTrash",
                "maxMenuRank"
            ],
            "Restaurant keys are not matching when connected as admin"
        );
    }

    public function testPostRestaurant(): void
    {
        // As guest

        $this->browser()
            ->post("/api/restaurants", [
                "json" => []
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user

        $user = UserFactory::createOne();
        $user2 = UserFactory::createOne();

        $browser = $this->browser(actingAs: $user)
            ->post("/api/restaurants", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        // TODO: make it so the owner is automatically defined when posting if the user is not an admin

        $json = $browser->post("/api/restaurants", [
                "json" => [
                    "name" => "My new restaurant"
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonMatches('owner', "/api/users/".$user->getId())
            ->json()->decoded()
        ;

        $this->assertSame(
            array_keys($json),
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "logo",
                "visible",
                "description",
                "restaurantMenus",
                "owner",
                "inTrash"
            ],
            "Restaurant keys are not matching when posting as normal user"
        );

        $browser->post("/api/restaurants", [
                "json" => [
                    "name" => "My new restaurant"
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('detail', "name: Vous possédez déjà un restaurant avec ce nom")
        ;

        // TODO: make it so the owner is always the connected user even if field value is something else, if the user is not an admin
        $browser->post("/api/restaurants", [
                "json" => [
                    "name" => "My shiny restaurant",
                    "owner" => "/api/users/".$user2->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonMatches('owner', "/api/users/".$user->getId())
        ;

        // As admin, on behalf of normal user

        $admin = UserFactory::new()->asAdmin()->create();
        $browser = $this->browser(actingAs: $admin)
            ->post("/api/restaurants", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $json = $browser->post("/api/restaurants", [
                "json" => [
                    "name" => "My new restaurant 2",
                    "owner" => "/api/users/".$user->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonMatches('owner', "/api/users/".$user->getId())
            ->json()->decoded()
        ;

        $this->assertSame(
            array_keys($json),
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "logo",
                "visible",
                "description",
                "restaurantMenus",
                "owner",
                "inTrash"
            ],
            "Restaurant keys are not matching when posting as admin"
        );
    }

    public function testDeleteRestaurant(): void
    {
        $user = UserFactory::createOne();
        $restaurant = RestaurantFactory::createOne(["owner" => $user]);
        $user2 = UserFactory::createOne();
        $restaurant2 = RestaurantFactory::createOne(["owner" => $user2]);

        // As guest

        $this->browser()
            ->delete("/api/restaurants/".$restaurant->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user

        $this->browser(actingAs: $user)
            ->delete("/api/restaurants/".$restaurant2->getId())
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/api/restaurants/".$restaurant->getId())
            ->dump()
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/api/restaurants/".$restaurant->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;

        // As admin

        $admin = UserFactory::new()->asAdmin()->create();

        $this->browser(actingAs: $admin)
            ->delete("/api/restaurants/".$restaurant2->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)
        ;
    }

    public function testPatchRestaurant(): void
    {
        // TODO: implement test
    }
}