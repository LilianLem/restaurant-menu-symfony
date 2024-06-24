<?php

namespace App\Tests\Functional;

use App\Entity\Product;
use App\Entity\Section;
use App\Factory\ProductFactory;
use App\Factory\SectionProductFactory;
use Carbon\Carbon;
use Override;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProductResourceTest extends ApiTestCase
{
    use ResetDatabase;
    use UserToSectionPopulateTrait {
        populate as traitPopulate;
    }

    private Product $productA1;
    private Product $productA2;
    private Product $productA3;
    private Product $productA4;
    private Product $productA5;
    private Product $productA6;
    private Product $productA7;
    private Product $productB1;
    private Product $productB2;
    private Product $productB3;
    private Product $productB4;
    private Product $productB5;
    private Product $productB6;
    private Product $productB7;
    private Product $productC1;
    private Product $productC2;

    #[Override] protected function setUp(): void
    {
        $this->populate();
    }

    private function populate(): void
    {
        $this->traitPopulate();

        // --- Product ---

        /** @var array<string, int> $productsData */
        $productsData = ["A" => 7, "B" => 7, "C" => 2];
        foreach($productsData as $letter => $count) {
            $i = 1;
            while($i <= $count) {
                $this->{"product".$letter.$i} = ProductFactory::createOne([
                    "name" => "Product ".$letter.$i
                ]);

                $i++;
            }
        }

        ProductFactory::find(["name" => "Product B4"])->setDeletedAt(new Carbon("yesterday"));
        ProductFactory::find(["name" => "Product B5"])->setDeletedAt(new Carbon("yesterday"));

        // --- SectionProduct ---

        $sectionProductData = [
            [$this->sectionA1, $this->productA1, false, 1],
            [$this->sectionA1, $this->productA2, true, 2],
            [$this->sectionA1, $this->productA3, true, 3],
            [$this->sectionA2, $this->productA2, false, 1],
            [$this->sectionA2, $this->productA3, false, 2],
            [$this->sectionA3, $this->productA4, true, 1],
            [$this->sectionA3, $this->productA5, false, 2],
            [$this->sectionA4, $this->productA6, true, 1],
            [$this->sectionA4, $this->productA7, false, 2],
            [$this->sectionB1, $this->productB1, true, 1],
            [$this->sectionB1, $this->productB6, true, 2],
            [$this->sectionB2, $this->productB2, true, 1],
            [$this->sectionB2, $this->productB3, false, 2],
            [$this->sectionB3, $this->productB4, true, 1, true],
            [$this->sectionB3, $this->productB5, false, 2, true],
            [$this->sectionB3, $this->productB6, true, 3, true],
            [$this->sectionB4, $this->productB6, true, 1],
            [$this->sectionB4, $this->productB7, false, 2],
            [$this->sectionC1, $this->productC1, true, 1],
            [$this->sectionC1, $this->productC2, false, 2]
        ];

        /** @var array{0: Section, 1: Product, 2: bool, 3: int, 4?: true} $data */
        foreach($sectionProductData as $data) {
            SectionProductFactory::createOne([
                "section" => $data[0],
                "product" => $data[1],
                "visible" => $data[2],
                "rank" => $data[3],
                "deletedAt" => isset($data[4]) ? new Carbon("yesterday") : null
            ]);
        }
    }

    public function testGetCollectionOfProducts(): void
    {
        // ------ //

        // As guest

        $this->browser()
            ->get("/products")
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $json = $this->browser(actingAs: $this->userA)
            ->get("/products")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 7)
            ->json()->decoded()
        ;
        $products = $json["hydra:member"];

        $this->assertSame(
            [
                "@id",
                "@type",
                "id",
                "name",
                "description",
                "price",
                "allergens",
                "versions",
                "productSections",
                "createdAt",
                "updatedAt"
            ],
            array_keys($products[0]),
            "Product keys are not matching when connected as normal user A"
        );

        $expectedProductIds = array_map(fn(Product $product) => $product->getId()->jsonSerialize(), [$this->productA1, $this->productA2, $this->productA3, $this->productA4, $this->productA5, $this->productA6, $this->productA7]);
        $productIds = array_map(fn(array $product) => $product["id"], $products);
        $this->assertEquals($expectedProductIds, $productIds, "The products in user A GET collection are not the ones expected");

        // As normal user B

        $json = $this->browser(actingAs: $this->userB)
            ->get("/products")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 5)
            ->json()->decoded()
        ;
        $products = $json["hydra:member"];

        $expectedProductIds = array_map(fn(Product $product) => $product->getId()->jsonSerialize(), [$this->productB1, $this->productB2, $this->productB3, $this->productB6, $this->productB7]);
        $productIds = array_map(fn(array $product) => $product["id"], $products);
        $this->assertEquals($expectedProductIds, $productIds, "The products in user B GET collection are not the ones expected");

        // As admin

        $json = $this->browser(actingAs: $this->admin)
            ->get("/products")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 14)
            ->json()->decoded()
        ;
        $products = $json["hydra:member"];

        $this->assertSame(
            [
                "@id",
                "@type",
                "id",
                "name",
                "description",
                "price",
                "allergens",
                "versions",
                "productSections",
                "createdAt",
                "updatedAt"
            ],
            array_keys($products[0]),
            "Product keys are not matching when connected as admin"
        );

        $expectedProductIds = array_map(fn(Product $product) => $product->getId()->jsonSerialize(), [$this->productA1, $this->productA2, $this->productA3, $this->productA4, $this->productA5, $this->productA6, $this->productA7, $this->productB1, $this->productB2, $this->productB3, $this->productB6, $this->productB7, $this->productC1, $this->productC2]);
        $productIds = array_map(fn(array $product) => $product["id"], $products);
        $this->assertEquals($expectedProductIds, $productIds, "The products in admin GET collection are not the ones expected");

        $this->browser(actingAs: $this->admin)
            ->get("/products?productSections.section.sectionMenu.menu.menuRestaurants.restaurant.owner=/users/{$this->userA->getId()}")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 7)
        ;

        $this->browser(actingAs: $this->admin)
            ->get("/products?productSections.section.sectionMenu.menu.menuRestaurants.restaurant.owner=/users/{$this->userB->getId()}")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 5)
        ;

        $json = $this->browser(actingAs: $this->admin)
            ->get("/products?productSections.section.sectionMenu.menu.menuRestaurants.restaurant.owner=/users/{$this->userC->getId()}")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 2)
            ->json()->decoded()
        ;
        $products = $json["hydra:member"];

        $expectedProductIds = array_map(fn(Product $product) => $product->getId()->jsonSerialize(), [$this->productC1, $this->productC2]);
        $productIds = array_map(fn(array $product) => $product["id"], $products);
        $this->assertEquals($expectedProductIds, $productIds, "The products in admin GET collection (filtered by user C ownership) are not the ones expected");
    }

    public function testGetProduct(): void
    {
        // As guest

        $expectedResultsData = [
            [$this->productA2, Response::HTTP_OK],
            [$this->productA1, Response::HTTP_UNAUTHORIZED],
            [$this->productA3, Response::HTTP_OK],
            [$this->productA4, Response::HTTP_UNAUTHORIZED],
            [$this->productA5, Response::HTTP_UNAUTHORIZED],
            [$this->productA6, Response::HTTP_UNAUTHORIZED],
            [$this->productA7, Response::HTTP_UNAUTHORIZED],
            [$this->productB1, Response::HTTP_OK],
            [$this->productB2, Response::HTTP_UNAUTHORIZED],
            [$this->productB3, Response::HTTP_UNAUTHORIZED],
            [$this->productB4, Response::HTTP_NOT_FOUND],
            [$this->productB5, Response::HTTP_NOT_FOUND],
            [$this->productB6, Response::HTTP_OK],
            [$this->productB7, Response::HTTP_UNAUTHORIZED],
            [$this->productC1, Response::HTTP_UNAUTHORIZED],
            [$this->productC2, Response::HTTP_UNAUTHORIZED]
        ];

        $browser = $this->browser();

        $product = null;
        /** @var array{0: Product, 1: int} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/products/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first product
            $product ??= $browser->json()->decoded();
        }

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "description",
                "price",
                "allergens",
                "versions",
                "productSections"
            ],
            array_keys($product),
            "Product keys are not matching when requesting as guest"
        );

        // As normal user A

        $expectedResultsData = [
            [$this->productA1, Response::HTTP_OK],
            [$this->productA2, Response::HTTP_OK],
            [$this->productA3, Response::HTTP_OK],
            [$this->productA4, Response::HTTP_OK],
            [$this->productA5, Response::HTTP_OK],
            [$this->productA6, Response::HTTP_OK],
            [$this->productA7, Response::HTTP_OK],
            [$this->productB1, Response::HTTP_OK],
            [$this->productB2, Response::HTTP_FORBIDDEN],
            [$this->productB3, Response::HTTP_FORBIDDEN],
            [$this->productB4, Response::HTTP_NOT_FOUND],
            [$this->productB5, Response::HTTP_NOT_FOUND],
            [$this->productB6, Response::HTTP_OK],
            [$this->productB7, Response::HTTP_FORBIDDEN],
            [$this->productC1, Response::HTTP_FORBIDDEN],
            [$this->productC2, Response::HTTP_FORBIDDEN]
        ];

        $browser = $this->browser(actingAs: $this->userA);

        $product = null;
        /** @var array{0: Product, 1: int} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/products/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first product
            $product ??= $browser->json()->decoded();
        }

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "description",
                "price",
                "allergens",
                "versions",
                "productSections",
                "createdAt",
                "updatedAt",
                "maxVersionRank"
            ],
            array_keys($product),
            "Product keys are not matching when requesting as normal user A"
        );

        // As normal user B

        $expectedResultsData = [
            [$this->productB1, Response::HTTP_OK],
            [$this->productA2, Response::HTTP_OK],
            [$this->productA1, Response::HTTP_FORBIDDEN],
            [$this->productA3, Response::HTTP_OK],
            [$this->productA4, Response::HTTP_FORBIDDEN],
            [$this->productA5, Response::HTTP_FORBIDDEN],
            [$this->productA6, Response::HTTP_FORBIDDEN],
            [$this->productA7, Response::HTTP_FORBIDDEN],
            [$this->productB2, Response::HTTP_OK],
            [$this->productB3, Response::HTTP_OK],
            [$this->productB4, Response::HTTP_NOT_FOUND],
            [$this->productB5, Response::HTTP_NOT_FOUND],
            [$this->productB6, Response::HTTP_OK],
            [$this->productB7, Response::HTTP_OK],
            [$this->productC1, Response::HTTP_FORBIDDEN],
            [$this->productC2, Response::HTTP_FORBIDDEN]
        ];

        $browser = $this->browser(actingAs: $this->userB);

        $productB = null;
        $productA = null;
        /** @var array{0: Product, 1: int} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/products/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first and second product
            if(!$productB) {
                $productB = $browser->json()->decoded();
            } elseif(!$productA) {
                $productA = $browser->json()->decoded();
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
                "price",
                "allergens",
                "versions",
                "productSections",
                "createdAt",
                "updatedAt",
                "maxVersionRank"
            ],
            array_keys($productB),
            "Product keys are not matching when requesting as normal user B (owner)"
        );

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "description",
                "price",
                "allergens",
                "versions",
                "productSections"
            ],
            array_keys($productA),
            "Product keys are not matching when requesting as normal user B (not owner)"
        );

        // As admin

        $expectedResultsData = [
            [$this->productA1, Response::HTTP_OK],
            [$this->productA2, Response::HTTP_OK],
            [$this->productA3, Response::HTTP_OK],
            [$this->productA4, Response::HTTP_OK],
            [$this->productA5, Response::HTTP_OK],
            [$this->productA6, Response::HTTP_OK],
            [$this->productA7, Response::HTTP_OK],
            [$this->productB1, Response::HTTP_OK],
            [$this->productB2, Response::HTTP_OK],
            [$this->productB3, Response::HTTP_OK],
            [$this->productB4, Response::HTTP_NOT_FOUND],
            [$this->productB5, Response::HTTP_NOT_FOUND],
            [$this->productB6, Response::HTTP_OK],
            [$this->productB7, Response::HTTP_OK],
            [$this->productC1, Response::HTTP_OK],
            [$this->productC2, Response::HTTP_OK]
        ];

        $browser = $this->browser(actingAs: $this->admin);

        $product = null;
        /** @var array{0: Product, 1: int} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/products/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first product
            $product ??= $browser->json()->decoded();
        }

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "name",
                "description",
                "price",
                "allergens",
                "versions",
                "productSections",
                "createdAt",
                "updatedAt",
                "maxVersionRank"
            ],
            array_keys($product),
            "Product keys are not matching when requesting as admin"
        );
    }

    public function testPostProduct(): void
    {
        // As guest

        $this->browser()
            ->post("/products", [
                "json" => []
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $browser = $this->browser(actingAs: $this->userA)
            ->post("/products", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $browser->post("/products", [
                "json" => [
                    "name" => "My new product"
                ]
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        // Creating a product in a "private" section (SectionProduct entity will be visible by default, but parent MenuSection is not visible and Menu has inTrash = true)
        $privateProduct = $browser->post("/products", [
            "json" => [
                "name" => "My new product",
                "firstSection" => "/sections/".$this->sectionA4->getId()
            ]
        ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
            ->json()->decoded()
        ;

        // Creating a product in a "public" section
        $product = $browser->post("/products", [
                "json" => [
                    "name" => "My new product",
                    "firstSection" => "/sections/".$this->sectionA1->getId()
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
                "price",
                "allergens",
                "versions",
                "productSections",
                "createdAt",
                "updatedAt"
            ],
            array_keys($product),
            "Product keys are not matching when posting as normal user A"
        );

        $this->browser(actingAs: $this->admin)
            ->get($product["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('productSections[0].section."@id"', "/sections/".$this->sectionA1->getId())
        ;

        // As normal user B

        // Checking if normal user B can access newly created user A-owned products, with private first and public next
        $browser = $this->browser(actingAs: $this->userB)
            ->get($privateProduct["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;
        $browser->get($product["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
        ;

        // Checking if normal user B can create a product on behalf of user A
        $browser->post("/products", [
                "json" => [
                    "name" => "My new product",
                    "firstSection" => "/sections/".$this->sectionA1->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('detail', "firstSection: Cette section ne vous appartient pas")
        ;

        // As admin, on behalf of normal user

        $browser = $this->browser(actingAs: $this->admin)->post("/products", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $product = $browser->post("/products", [
                "json" => [
                    "name" => "My new product",
                    "firstSection" => "/sections/".$this->sectionA1->getId()
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
                "price",
                "allergens",
                "versions",
                "productSections",
                "createdAt",
                "updatedAt"
            ],
            array_keys($product),
            "Product keys are not matching when posting as admin"
        );

        $browser->get($product["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('productSections[0].section."@id"', "/sections/".$this->sectionA1->getId())
        ;
    }

    public function testHardDeleteProduct(): void
    {
        // As guest

        $this->browser()
            ->delete("/products/".$this->productA2->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $this->browser(actingAs: $this->userA)
            ->delete("/products/".$this->productB1->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/products/".$this->productA1->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/products/".$this->productA1->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;

        // As normal user B

        $this->browser(actingAs: $this->userB)
            ->delete("/products/".$this->productA2->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/products/".$this->productB2->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/products/".$this->productB2->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)

            ->delete("/products/".$this->productB4->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;

        // As admin

        $this->browser(actingAs: $this->admin)
            ->delete("/products/".$this->productA2->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/products/".$this->productA2->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;
    }

    public function testPatchProduct(): void
    {
        // TODO: implement test
    }
}