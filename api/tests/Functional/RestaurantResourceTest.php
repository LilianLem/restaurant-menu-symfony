<?php

namespace App\Tests\Functional;

use App\Entity\Restaurant;
use App\Factory\RestaurantFactory;
use App\Factory\UserFactory;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\Test\ResetDatabase;

class RestaurantResourceTest extends ApiTestCase
{
    use ResetDatabase;

    public function testGetCollectionOfRestaurants(): void
    {
        RestaurantFactory::createMany(8, ["owner" => UserFactory::createOne()]);
        $user = UserFactory::createOne();
        $ownedRestaurants = RestaurantFactory::createMany(2, ["owner" => $user]);

        /** @var Restaurant|Proxy<Restaurant> $restaurant */
        $ownedRestaurantIds = array_map(fn(Restaurant|Proxy $restaurant) => $restaurant->getId()->jsonSerialize(), $ownedRestaurants);

        $admin = UserFactory::new()->asAdmin()->create();

        // As guest

        $this->browser()
            ->get("/restaurants")
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user

        $json = $this->browser(actingAs: $user)
            ->get("/restaurants")
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
                "inTrash"
            ],
            "Restaurant keys are not matching when connected as normal user"
        );

        foreach($restaurants as $restaurant) {
            $this->assertContains($restaurant["id"], $ownedRestaurantIds, "At least one restaurant retrieved when connected as normal user is not self-owned");
        }

        // As admin

        $json = $this->browser(actingAs: $admin)
            ->get("/restaurants")
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
            ->get("/restaurants/".$restaurantB->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->get("/restaurants/".$restaurantC->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->get("/restaurants/".$restaurantA->getId())
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
                "description",
                "restaurantMenus"
            ],
            "Restaurant keys are not matching when requesting as guest"
        );

        // As normal user (owner)

        $restaurant = $this->browser(actingAs: $user)
            ->get("/restaurants/".$restaurantB->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->get("/restaurants/".$restaurantC->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->get("/restaurants/".$restaurantA->getId())
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
                "inTrash",
                "maxMenuRank"
            ],
            "Restaurant keys are not matching when connected as normal user (owner)"
        );

        // As normal user (not owner)

        $restaurant = $this->browser(actingAs: $user2)
            ->get("/restaurants/".$restaurantB->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->get("/restaurants/".$restaurantC->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->get("/restaurants/".$restaurantA->getId())
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
                "description",
                "restaurantMenus"
            ],
            "Restaurant keys are not matching when requesting as normal user (not owner)"
        );

        // As admin

        $admin = UserFactory::new()->asAdmin()->create();

        $restaurant = $this->browser(actingAs: $admin)
            ->get("/restaurants/".$restaurantB->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->get("/restaurants/".$restaurantC->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->get("/restaurants/".$restaurantA->getId())
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
            ->post("/restaurants", [
                "json" => []
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user

        $user = UserFactory::createOne();
        $user2 = UserFactory::createOne();
        $admin = UserFactory::new()->asAdmin()->create();

        $browser = $this->browser(actingAs: $user)
            ->post("/restaurants", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $restaurant = $browser->post("/restaurants", [
                "json" => [
                    "name" => "My new restaurant"
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
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
            "Restaurant keys are not matching when posting as normal user"
        );

        $this->browser(actingAs: $admin)
            ->get($restaurant["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('owner."@id"', "/users/".$user->getId())
        ;

        $this->browser(actingAs: $user)
            ->post("/restaurants", [
                "json" => [
                    "name" => "My new restaurant"
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('detail', "name: Vous possédez déjà un restaurant avec ce nom")
        ;

        // Check if owner field is ignored (it should only be processed if user is admin)
        $restaurant = $browser->post("/restaurants", [
                "json" => [
                    "name" => "My shiny restaurant",
                    "owner" => "/users/".$user2->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
            ->json()->decoded()
        ;
        $browser = $this->browser(actingAs: $admin)
            ->get($restaurant["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('owner."@id"', "/users/".$user->getId())
        ;

        // As admin, on behalf of normal user

        $browser->post("/restaurants", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $restaurant = $browser->post("/restaurants", [
                "json" => [
                    "name" => "My new restaurant 2",
                    "owner" => "/users/".$user->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonMatches('owner', "/users/".$user->getId())
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
            ->delete("/restaurants/".$restaurant->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user

        $this->browser(actingAs: $user)
            ->delete("/restaurants/".$restaurant2->getId())
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/restaurants/".$restaurant->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/restaurants/".$restaurant->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;

        // As admin

        $admin = UserFactory::new()->asAdmin()->create();

        $this->browser(actingAs: $admin)
            ->delete("/restaurants/".$restaurant2->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)
        ;
    }

    public function testPatchRestaurant(): void
    {
        // TODO: implement test
    }
}