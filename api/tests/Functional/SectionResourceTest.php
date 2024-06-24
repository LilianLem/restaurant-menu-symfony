<?php

namespace App\Tests\Functional;

use App\Entity\Section;
use Override;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

class SectionResourceTest extends ApiTestCase
{
    use ResetDatabase, UserToSectionPopulateTrait;

    #[Override] protected function setUp(): void
    {
        $this->populate();
    }

    public function testGetCollectionOfSections(): void
    {
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
        /** @var array{0: Section, 1: int} $data */
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
        /** @var array{0: Section, 1: int} $data */
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
        /** @var array{0: Section, 1: int} $data */
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
        /** @var array{0: Section, 1: int} $data */
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