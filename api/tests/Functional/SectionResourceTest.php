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
use Override;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

class SectionResourceTest extends ApiTestCase
{
    use ResetDatabase;

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

    public function testGetCollectionOfSections(): void
    {
        // ------ //

        // As guest

        $this->browser()
            ->get("/sections")
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $json = $this->browser(actingAs: $this->userA)
            ->get("/sections")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 4)
            ->json()->decoded()
        ;
        $sections = $json["hydra:member"];

        $this->assertSame(
            [
                "@id",
                "@type",
                "id",
                "name",
                "price",
                "sectionProducts",
                "sectionMenu",
                "createdAt",
                "updatedAt"
            ],
            array_keys($sections[0]),
            "Section keys are not matching when connected as normal user A"
        );

        $expectedSectionIds = array_map(fn(Section $section) => $section->getId()->jsonSerialize(), [$this->sectionA1, $this->sectionA2, $this->sectionA3, $this->sectionA4]);
        $sectionIds = array_map(fn(array $section) => $section["id"], $sections);
        $this->assertEquals($expectedSectionIds, $sectionIds, "The sections in user A GET collection are not the ones expected");

        // As normal user B

        $json = $this->browser(actingAs: $this->userB)
            ->get("/sections")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 3)
            ->json()->decoded()
        ;
        $sections = $json["hydra:member"];

        $expectedSectionIds = array_map(fn(Section $section) => $section->getId()->jsonSerialize(), [$this->sectionB1, $this->sectionB2, $this->sectionB4]);
        $sectionIds = array_map(fn(array $section) => $section["id"], $sections);
        $this->assertEquals($expectedSectionIds, $sectionIds, "The sections in user B GET collection are not the ones expected");

        // As admin

        $json = $this->browser(actingAs: $this->admin)
            ->get("/sections")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 8)
            ->json()->decoded()
        ;
        $sections = $json["hydra:member"];

        $this->assertSame(
            [
                "@id",
                "@type",
                "id",
                "name",
                "price",
                "sectionProducts",
                "sectionMenu",
                "createdAt",
                "updatedAt"
            ],
            array_keys($sections[0]),
            "Section keys are not matching when connected as admin"
        );

        $expectedSectionIds = array_map(fn(Section $section) => $section->getId()->jsonSerialize(), [$this->sectionA1, $this->sectionA2, $this->sectionA3, $this->sectionA4, $this->sectionB1, $this->sectionB2, $this->sectionB4, $this->sectionC1]);
        $sectionIds = array_map(fn(array $section) => $section["id"], $sections);
        $this->assertEquals($expectedSectionIds, $sectionIds, "The sections in admin GET collection are not the ones expected");

        $this->browser(actingAs: $this->admin)
            ->get("/sections?sectionMenu.menu.menuRestaurants.restaurant.owner=/users/{$this->userA->getId()}")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 4)
        ;

        $this->browser(actingAs: $this->admin)
            ->get("/sections?sectionMenu.menu.menuRestaurants.restaurant.owner=/users/{$this->userB->getId()}")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 3)
        ;

        $json = $this->browser(actingAs: $this->admin)
            ->get("/sections?sectionMenu.menu.menuRestaurants.restaurant.owner=/users/{$this->userC->getId()}")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 1)
            ->json()->decoded()
        ;
        $sections = $json["hydra:member"];

        $this->assertEquals($this->sectionC1->getId()->jsonSerialize(), $sections[0]["id"], "The section in admin GET collection (filtered by user C ownership) is not the one expected");
    }

    public function testGetSection(): void
    {
        // As guest

        $expectedResultsData = [
            [$this->sectionA1, Response::HTTP_OK],
            [$this->sectionA2, Response::HTTP_OK],
            [$this->sectionA3, Response::HTTP_UNAUTHORIZED],
            [$this->sectionA4, Response::HTTP_UNAUTHORIZED],
            [$this->sectionB1, Response::HTTP_OK],
            [$this->sectionB2, Response::HTTP_UNAUTHORIZED],
            [$this->sectionB3, Response::HTTP_NOT_FOUND],
            [$this->sectionB4, Response::HTTP_UNAUTHORIZED],
            [$this->sectionC1, Response::HTTP_UNAUTHORIZED]
        ];

        $browser = $this->browser();

        $section = null;
        /** @var array{0: Section, 1: string} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/sections/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first section
            $section ??= $browser->json()->decoded();
        }

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "price",
                "sectionMenu",
                "sectionProducts"
            ],
            array_keys($section),
            "Section keys are not matching when requesting as guest"
        );

        // As normal user A

        $expectedResultsData = [
            [$this->sectionA1, Response::HTTP_OK],
            [$this->sectionA2, Response::HTTP_OK],
            [$this->sectionA3, Response::HTTP_OK],
            [$this->sectionA4, Response::HTTP_OK],
            [$this->sectionB1, Response::HTTP_OK],
            [$this->sectionB2, Response::HTTP_FORBIDDEN],
            [$this->sectionB3, Response::HTTP_NOT_FOUND],
            [$this->sectionB4, Response::HTTP_FORBIDDEN],
            [$this->sectionC1, Response::HTTP_FORBIDDEN]
        ];

        $browser = $this->browser(actingAs: $this->userA);

        $section = null;
        /** @var array{0: Section, 1: string} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/sections/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first section
            $section ??= $browser->json()->decoded();
        }

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "price",
                "sectionProducts",
                "sectionMenu",
                "createdAt",
                "updatedAt",
                "maxProductRank"
            ],
            array_keys($section),
            "Section keys are not matching when requesting as normal user A"
        );

        // As normal user B

        $expectedResultsData = [
            [$this->sectionB1, Response::HTTP_OK],
            [$this->sectionA1, Response::HTTP_OK],
            [$this->sectionA2, Response::HTTP_OK],
            [$this->sectionA3, Response::HTTP_FORBIDDEN],
            [$this->sectionA4, Response::HTTP_FORBIDDEN],
            [$this->sectionB2, Response::HTTP_OK],
            [$this->sectionB3, Response::HTTP_NOT_FOUND],
            [$this->sectionB4, Response::HTTP_OK],
            [$this->sectionC1, Response::HTTP_FORBIDDEN]
        ];

        $browser = $this->browser(actingAs: $this->userB);

        $sectionB = null;
        $sectionA = null;
        /** @var array{0: Section, 1: string} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/sections/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first and second section
            if(!$sectionB) {
                $sectionB = $browser->json()->decoded();
            } elseif(!$sectionA) {
                $sectionA = $browser->json()->decoded();
            }
        }

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "price",
                "sectionProducts",
                "sectionMenu",
                "createdAt",
                "updatedAt",
                "maxProductRank"
            ],
            array_keys($sectionB),
            "Section keys are not matching when requesting as normal user B (owner)"
        );

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "price",
                "sectionMenu",
                "sectionProducts"
            ],
            array_keys($sectionA),
            "Section keys are not matching when requesting as normal user B (not owner)"
        );

        // As admin

        $expectedResultsData = [
            [$this->sectionA1, Response::HTTP_OK],
            [$this->sectionA2, Response::HTTP_OK],
            [$this->sectionA3, Response::HTTP_OK],
            [$this->sectionA4, Response::HTTP_OK],
            [$this->sectionB1, Response::HTTP_OK],
            [$this->sectionB2, Response::HTTP_OK],
            [$this->sectionB3, Response::HTTP_NOT_FOUND],
            [$this->sectionB4, Response::HTTP_OK],
            [$this->sectionC1, Response::HTTP_OK]
        ];

        $browser = $this->browser(actingAs: $this->admin);

        $section = null;
        /** @var array{0: Section, 1: string} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/sections/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first section
            $section ??= $browser->json()->decoded();
        }

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "price",
                "sectionProducts",
                "sectionMenu",
                "createdAt",
                "updatedAt",
                "maxProductRank"
            ],
            array_keys($section),
            "Section keys are not matching when requesting as admin"
        );
    }

    public function testPostSection(): void
    {
        // As guest

        $this->browser()
            ->post("/sections", [
                "json" => []
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $browser = $this->browser(actingAs: $this->userA)
            ->post("/sections", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $browser->post("/sections", [
                "json" => [
                    "name" => "My new section"
                ]
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $section = $browser->post("/sections", [
                "json" => [
                    "name" => "My new section",
                    "menu" => "/menus/".$this->menuA1->getId()
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
                "price",
                "sectionProducts",
                "sectionMenu",
                "createdAt",
                "updatedAt"
            ],
            array_keys($section),
            "Section keys are not matching when posting as normal user A"
        );

        $this->browser(actingAs: $this->admin)
            ->get($section["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('sectionMenu.menu."@id"', "/menus/".$this->menuA1->getId())
        ;

        // As normal user B

        // Checking if normal user B can access newly created user A-owned section
        $browser = $this->browser(actingAs: $this->userB)
            ->get($section["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;

        // Checking if normal user B can create a section on behalf of user A
        $browser->post("/sections", [
                "json" => [
                    "name" => "My new section",
                    "menu" => "/menus/".$this->menuA1->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('detail', "menu: Ce menu ne vous appartient pas")
        ;

        // As admin, on behalf of normal user

        $browser = $this->browser(actingAs: $this->admin)->post("/sections", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $section = $browser->post("/sections", [
                "json" => [
                    "name" => "My new section",
                    "menu" => "/menus/".$this->menuA1->getId()
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
                "price",
                "sectionProducts",
                "sectionMenu",
                "createdAt",
                "updatedAt"
            ],
            array_keys($section),
            "Section keys are not matching when posting as admin"
        );

        $browser->get($section["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('sectionMenu.menu."@id"', "/menus/".$this->menuA1->getId())
        ;
    }

    public function testHardDeleteSection(): void
    {
        // As guest

        $this->browser()
            ->delete("/sections/".$this->sectionA2->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $this->browser(actingAs: $this->userA)
            ->delete("/sections/".$this->sectionB1->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/sections/".$this->sectionA2->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/sections/".$this->sectionA2->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;

        // As normal user B

        $this->browser(actingAs: $this->userB)
            ->delete("/sections/".$this->sectionA1->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/sections/".$this->sectionB2->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/sections/".$this->sectionB2->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)

            ->delete("/sections/".$this->sectionB3->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;

        // As admin

        $this->browser(actingAs: $this->admin)
            ->delete("/sections/".$this->sectionA1->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/sections/".$this->sectionA1->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;
    }

    public function testPatchSection(): void
    {
        // TODO: implement test
    }
}