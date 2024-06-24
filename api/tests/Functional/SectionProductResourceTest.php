<?php

namespace App\Tests\Functional;

use App\Entity\SectionProduct;
use App\Factory\SectionProductFactory;
use Override;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

class SectionProductResourceTest extends ApiTestCase
{
    use ResetDatabase;
    use UserToProductPopulateTrait;

    /** @var SectionProduct[] $userASectionProducts */
    private array $userASectionProducts;

    /** @var SectionProduct[] $userBSectionProducts */
    private array $userBSectionProducts;

    /** @var SectionProduct[] $userCSectionProducts */
    private array $userCSectionProducts;

    #[Override] protected function setUp(): void
    {
        $this->populate();

        $this->userASectionProducts = SectionProductFactory::findBy(["section" => [$this->sectionA1, $this->sectionA2, $this->sectionA3, $this->sectionA4]]);
        $this->userBSectionProducts = SectionProductFactory::findBy(["section" => [$this->sectionB1, $this->sectionB2, $this->sectionB3, $this->sectionB4]]);
        $this->userCSectionProducts = SectionProductFactory::findBy(["section" => [$this->sectionC1]]);
    }

    public function testPostSectionProduct(): void
    {
        // As guest

        $this->browser()
            ->post("/section_products", [
                "json" => []
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $browser = $this->browser(actingAs: $this->userA)
            ->post("/section_products", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $browser->post("/section_products", [
                "json" => [
                    "section" => "/sections/".$this->sectionA3->getId()
                ]
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        // Linking an already "private" product to a "private" section (SectionProduct entity will be visible by default, but parent MenuSection is not visible and Menu has inTrash = true)
        $browser->post("/section_products", [
                "json" => [
                    "section" => "/sections/".$this->sectionA3->getId(),
                    "product" => "/products/".$this->productA7->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
        ;

        // Linking a current "private" product to a "public" section, so that it can now be viewed by anyone
        $sectionProduct = $browser->post("/section_products", [
                "json" => [
                    "section" => "/sections/".$this->sectionA1->getId(),
                    "product" => "/products/".$this->productA6->getId()
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
                "section",
                "product",
                "visible",
                "rank",
                "createdAt",
                "updatedAt"
            ],
            array_keys($sectionProduct),
            "SectionProduct keys are not matching when posting as normal user A"
        );

        $browser->get("/products/".$this->productA7->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('productSections[1].section."@id"', "/sections/".$this->sectionA3->getId())
        ;

        $browser->get("/products/".$this->productA6->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('productSections[1].section."@id"', "/sections/".$this->sectionA1->getId())
        ;

        // As normal user B

        // Checking if normal user B can access newly created user A-owned products, with private first and public next
        $browser = $this->browser(actingAs: $this->userB)
            ->get("/products/".$this->productA7->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;

        $browser->get("/products/".$this->productA6->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('productSections[0].section."@id"', "/sections/".$this->sectionA1->getId())
        ;

        // Checking if normal user B can link an owned product to a section owned by user A, and if user B can link an owned section to a product owned by user B
        $browser->post("/section_products", [
                "json" => [
                    "section" => "/sections/".$this->sectionA1->getId(),
                    "product" => "/products/".$this->productB1->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('detail', "section: Cette section ne vous appartient pas")
        ;

        $browser->post("/section_products", [
                "json" => [
                    "section" => "/sections/".$this->sectionB1->getId(),
                    "product" => "/products/".$this->productA1->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('detail', "product: Ce produit ne vous appartient pas")
        ;

        // As admin, on behalf of normal user

        $browser = $this->browser(actingAs: $this->admin)->post("/section_products", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $sectionProduct = $browser->post("/section_products", [
                "json" => [
                    "section" => "/sections/".$this->sectionB4->getId(),
                    "product" => "/products/".$this->productB3->getId()
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
                "section",
                "product",
                "visible",
                "rank",
                "createdAt",
                "updatedAt"
            ],
            array_keys($sectionProduct),
            "SectionProduct keys are not matching when posting as admin"
        );

        $browser->get("/products/".$this->productB3->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('productSections[1].section."@id"', "/sections/".$this->sectionB4->getId())
        ;
    }

    // TODO: simplify code (too cryptic because array indexes only rely on objects creation order)
    // TODO: add GET /products/{id} requests to assert products are either still visible with SectionProduct removed from productSections property, or not visible anymore
    public function testHardDeleteSectionProduct(): void
    {
        // As guest

        $this->browser()
            ->delete("/section_products/".$this->userASectionProducts[0]->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $this->browser(actingAs: $this->userA)
            ->delete("/section_products/".$this->userBSectionProducts[0]->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/section_products/".$this->userASectionProducts[0]->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/section_products/".$this->userASectionProducts[0]->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;

        // As normal user B

        $this->browser(actingAs: $this->userB)
            ->delete("/products/".$this->userASectionProducts[1]->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/products/".$this->userBSectionProducts[0]->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/products/".$this->userBSectionProducts[0]->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)

            ->delete("/products/".$this->userBSectionProducts[3]->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;

        // As admin

        $this->browser(actingAs: $this->admin)
            ->delete("/products/".$this->userASectionProducts[2]->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/products/".$this->userASectionProducts[2]->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;
    }

    public function testPatchSectionProduct(): void
    {
        // TODO: implement test
    }
}