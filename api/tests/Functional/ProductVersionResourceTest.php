<?php

namespace App\Tests\Functional;

use App\Entity\ProductVersion;
use App\Factory\ProductVersionFactory;
use Carbon\Carbon;
use Override;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProductVersionResourceTest extends ApiTestCase
{
    use ResetDatabase;
    use UserToProductPopulateTrait {
        populate as traitPopulate;
    }

    /** @var array<string, array{1: ProductVersion, 2: ProductVersion}> $productVersions */
    private array $productVersions = [];

    /** @var array<string, array{1: ProductVersion, 2: ProductVersion}> $visibleVersions */
    private array $visibleVersions = [];

    #[Override] protected function setUp(): void
    {
        $this->populate();
    }

    private function populate(): void
    {
        $this->traitPopulate();

        foreach(self::PRODUCTS_DATA as $letter => $count) {
            $i = 1;
            while($i <= $count) {
                $this->productVersions[$letter.$i] = [
                    1 => ProductVersionFactory::createOne([
                        "name" => "Version ".$letter.$i."-1",
                        "product" => $this->{"product".$letter.$i},
                        "visible" => true,
                        "rank" => 1
                    ]),
                    2 => ProductVersionFactory::createOne([
                        "name" => "Version ".$letter.$i."-2",
                        "product" => $this->{"product".$letter.$i},
                        "visible" => false,
                        "rank" => 2
                    ])
                ];

                $i++;
            }
        }

        $this->productVersions["B4"][1]->setDeletedAt(new Carbon("yesterday"));
        $this->productVersions["B4"][2]->setDeletedAt(new Carbon("yesterday"));
        $this->productVersions["B5"][1]->setDeletedAt(new Carbon("yesterday"));
        $this->productVersions["B5"][2]->setDeletedAt(new Carbon("yesterday"));

        $this->visibleVersions = [];
        foreach($this->productVersions as $versions) {
            if($versions[1]->isDeleted()) {
                continue;
            }

            array_push($this->visibleVersions, ...$versions);
        }
    }

    public function testGetCollectionOfProductVersions(): void
    {
        // As guest

        $this->browser()
            ->get("/product_versions")
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $this->browser(actingAs: $this->userA)
            ->get("/product_versions")
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;

        // As admin

        $json = $this->browser(actingAs: $this->admin)
            ->get("/product_versions")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 28)
            ->json()->decoded()
        ;
        $productVersions = $json["hydra:member"];

        $this->assertSame(
            [
                "@id",
                "@type",
                "id",
                "product",
                "name",
                "price",
                "visible",
                "rank",
                "createdAt",
                "updatedAt"
            ],
            array_keys($productVersions[0]),
            "ProductVersion keys are not matching when connected as admin"
        );

        $expectedProductVersionIds = array_map(fn(ProductVersion $productVersion) => $productVersion->getId()->jsonSerialize(), $this->visibleVersions);
        $productVersionIds = array_map(fn(array $productVersion) => $productVersion["id"], $productVersions);
        $this->assertEquals($expectedProductVersionIds, $productVersionIds, "The product versions in admin GET collection are not the ones expected");
    }

    public function testGetProductVersion(): void
    {
        // As guest

        $expectedResultsData = [
            [$this->productVersions["A2"][1], Response::HTTP_OK],
            [$this->productVersions["A1"][1], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["A1"][2], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["A2"][2], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["A3"][1], Response::HTTP_OK],
            [$this->productVersions["A3"][2], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["A4"][1], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["A4"][2], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["A5"][1], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["A5"][2], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["A6"][1], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["A6"][2], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["A7"][1], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["A7"][2], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["B1"][1], Response::HTTP_OK],
            [$this->productVersions["B1"][2], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["B2"][1], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["B2"][2], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["B3"][1], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["B3"][2], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["B4"][1], Response::HTTP_NOT_FOUND],
            [$this->productVersions["B4"][2], Response::HTTP_NOT_FOUND],
            [$this->productVersions["B5"][1], Response::HTTP_NOT_FOUND],
            [$this->productVersions["B5"][2], Response::HTTP_NOT_FOUND],
            [$this->productVersions["B6"][1], Response::HTTP_OK],
            [$this->productVersions["B6"][2], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["B7"][1], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["B7"][2], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["C1"][1], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["C1"][2], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["C2"][1], Response::HTTP_UNAUTHORIZED],
            [$this->productVersions["C2"][2], Response::HTTP_UNAUTHORIZED],
        ];

        $browser = $this->browser();

        $productVersion = null;
        /** @var array{0: ProductVersion, 1: int} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/product_versions/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first product version
            $productVersion ??= $browser->json()->decoded();
        }

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "product",
                "name",
                "price",
                "visible",
                "rank"
            ],
            array_keys($productVersion),
            "ProductVersion keys are not matching when requesting as guest"
        );

        // As normal user A

        $expectedResultsData = [
            [$this->productVersions["A1"][1], Response::HTTP_OK],
            [$this->productVersions["A1"][2], Response::HTTP_OK],
            [$this->productVersions["A2"][1], Response::HTTP_OK],
            [$this->productVersions["A2"][2], Response::HTTP_OK],
            [$this->productVersions["A3"][1], Response::HTTP_OK],
            [$this->productVersions["A3"][2], Response::HTTP_OK],
            [$this->productVersions["A4"][1], Response::HTTP_OK],
            [$this->productVersions["A4"][2], Response::HTTP_OK],
            [$this->productVersions["A5"][1], Response::HTTP_OK],
            [$this->productVersions["A5"][2], Response::HTTP_OK],
            [$this->productVersions["A6"][1], Response::HTTP_OK],
            [$this->productVersions["A6"][2], Response::HTTP_OK],
            [$this->productVersions["A7"][1], Response::HTTP_OK],
            [$this->productVersions["A7"][2], Response::HTTP_OK],
            [$this->productVersions["B1"][1], Response::HTTP_OK],
            [$this->productVersions["B1"][2], Response::HTTP_FORBIDDEN],
            [$this->productVersions["B2"][1], Response::HTTP_FORBIDDEN],
            [$this->productVersions["B2"][2], Response::HTTP_FORBIDDEN],
            [$this->productVersions["B3"][1], Response::HTTP_FORBIDDEN],
            [$this->productVersions["B3"][2], Response::HTTP_FORBIDDEN],
            [$this->productVersions["B4"][1], Response::HTTP_NOT_FOUND],
            [$this->productVersions["B4"][2], Response::HTTP_NOT_FOUND],
            [$this->productVersions["B5"][1], Response::HTTP_NOT_FOUND],
            [$this->productVersions["B5"][2], Response::HTTP_NOT_FOUND],
            [$this->productVersions["B6"][1], Response::HTTP_OK],
            [$this->productVersions["B6"][2], Response::HTTP_FORBIDDEN],
            [$this->productVersions["B7"][1], Response::HTTP_FORBIDDEN],
            [$this->productVersions["B7"][2], Response::HTTP_FORBIDDEN],
            [$this->productVersions["C1"][1], Response::HTTP_FORBIDDEN],
            [$this->productVersions["C1"][2], Response::HTTP_FORBIDDEN],
            [$this->productVersions["C2"][1], Response::HTTP_FORBIDDEN],
            [$this->productVersions["C2"][2], Response::HTTP_FORBIDDEN],
        ];

        $browser = $this->browser(actingAs: $this->userA);

        $productVersion = null;
        /** @var array{0: ProductVersion, 1: int} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/product_versions/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first product version
            $productVersion ??= $browser->json()->decoded();
        }

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "product",
                "name",
                "price",
                "visible",
                "rank",
                "createdAt",
                "updatedAt"
            ],
            array_keys($productVersion),
            "ProductVersion keys are not matching when requesting as normal user A"
        );

        // As normal user B

        $expectedResultsData = [
            [$this->productVersions["B1"][1], Response::HTTP_OK],
            [$this->productVersions["A2"][1], Response::HTTP_OK],
            [$this->productVersions["A1"][1], Response::HTTP_FORBIDDEN],
            [$this->productVersions["A1"][2], Response::HTTP_FORBIDDEN],
            [$this->productVersions["A2"][2], Response::HTTP_FORBIDDEN],
            [$this->productVersions["A3"][1], Response::HTTP_OK],
            [$this->productVersions["A3"][2], Response::HTTP_FORBIDDEN],
            [$this->productVersions["A4"][1], Response::HTTP_FORBIDDEN],
            [$this->productVersions["A4"][2], Response::HTTP_FORBIDDEN],
            [$this->productVersions["A5"][1], Response::HTTP_FORBIDDEN],
            [$this->productVersions["A5"][2], Response::HTTP_FORBIDDEN],
            [$this->productVersions["A6"][1], Response::HTTP_FORBIDDEN],
            [$this->productVersions["A6"][2], Response::HTTP_FORBIDDEN],
            [$this->productVersions["A7"][1], Response::HTTP_FORBIDDEN],
            [$this->productVersions["A7"][2], Response::HTTP_FORBIDDEN],
            [$this->productVersions["B1"][2], Response::HTTP_OK],
            [$this->productVersions["B2"][1], Response::HTTP_OK],
            [$this->productVersions["B2"][2], Response::HTTP_OK],
            [$this->productVersions["B3"][1], Response::HTTP_OK],
            [$this->productVersions["B3"][2], Response::HTTP_OK],
            [$this->productVersions["B4"][1], Response::HTTP_NOT_FOUND],
            [$this->productVersions["B4"][2], Response::HTTP_NOT_FOUND],
            [$this->productVersions["B5"][1], Response::HTTP_NOT_FOUND],
            [$this->productVersions["B5"][2], Response::HTTP_NOT_FOUND],
            [$this->productVersions["B6"][1], Response::HTTP_OK],
            [$this->productVersions["B6"][2], Response::HTTP_OK],
            [$this->productVersions["B7"][1], Response::HTTP_OK],
            [$this->productVersions["B7"][2], Response::HTTP_OK],
            [$this->productVersions["C1"][1], Response::HTTP_FORBIDDEN],
            [$this->productVersions["C1"][2], Response::HTTP_FORBIDDEN],
            [$this->productVersions["C2"][1], Response::HTTP_FORBIDDEN],
            [$this->productVersions["C2"][2], Response::HTTP_FORBIDDEN],
        ];

        $browser = $this->browser(actingAs: $this->userB);

        $productVersionB = null;
        $productVersionA = null;
        /** @var array{0: ProductVersion, 1: int} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/product_versions/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first and second product version
            if(!$productVersionB) {
                $productVersionB = $browser->json()->decoded();
            } elseif(!$productVersionA) {
                $productVersionA = $browser->json()->decoded();
            }
        }

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "product",
                "name",
                "price",
                "visible",
                "rank",
                "createdAt",
                "updatedAt"
            ],
            array_keys($productVersionB),
            "ProductVersion keys are not matching when requesting as normal user B (owner)"
        );

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "product",
                "name",
                "price",
                "visible",
                "rank"
            ],
            array_keys($productVersionA),
            "ProductVersion keys are not matching when requesting as normal user B (not owner)"
        );

        // As admin

        $expectedResultsData = [
            ...array_map(fn(ProductVersion $version) => [$version, Response::HTTP_OK], $this->visibleVersions),
            [$this->productVersions["B4"][1], Response::HTTP_NOT_FOUND],
            [$this->productVersions["B4"][2], Response::HTTP_NOT_FOUND],
            [$this->productVersions["B5"][1], Response::HTTP_NOT_FOUND],
            [$this->productVersions["B5"][2], Response::HTTP_NOT_FOUND],
        ];

        $browser = $this->browser(actingAs: $this->admin);

        $productVersion = null;
        /** @var array{0: ProductVersion, 1: int} $data */
        foreach($expectedResultsData as $data) {
            $browser->get("/product_versions/".$data[0]->getId())
                ->assertJson()
                ->assertStatus($data[1])
            ;

            // Only retrieve first product version
            $productVersion ??= $browser->json()->decoded();
        }

        $this->assertSame(
            [
                "@context",
                "@id",
                "@type",
                "id",
                "product",
                "name",
                "price",
                "visible",
                "rank",
                "createdAt",
                "updatedAt"
            ],
            array_keys($productVersion),
            "ProductVersion keys are not matching when requesting as admin"
        );
    }

    public function testPostProductVersion(): void
    {
        // As guest

        $this->browser()
            ->post("/product_versions", [
                "json" => []
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $browser = $this->browser(actingAs: $this->userA)
            ->post("/product_versions", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $browser->post("/product_versions", [
                "json" => [
                    "name" => "My new product version"
                ]
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        // Creating a version of a "private" product (ProductVersion entity will be visible by default, but parent SectionProduct is not visible and Menu has inTrash = true)
        $privateProductVersion = $browser->post("/product_versions", [
                "json" => [
                    "name" => "My new product version",
                    "product" => "/products/".$this->productA7->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
            ->json()->decoded()
        ;

        // Creating a product in a "public" section
        $productVersion = $browser->post("/product_versions", [
                "json" => [
                    "name" => "My new product version",
                    "product" => "/products/".$this->productA2->getId()
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
                "product",
                "name",
                "price",
                "visible",
                "rank",
                "createdAt",
                "updatedAt"
            ],
            array_keys($productVersion),
            "ProductVersion keys are not matching when posting as normal user A"
        );

        $browser->get($productVersion["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('product."@id"', "/products/".$this->productA2->getId())
        ;

        // As normal user B

        // Checking if normal user B can access newly created user A-owned product versions, with private first and public next
        $browser = $this->browser(actingAs: $this->userB)
            ->get($privateProductVersion["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;
        $browser->get($productVersion["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
        ;

        // Checking if normal user B can create a product on behalf of user A
        $browser->post("/product_versions", [
                "json" => [
                    "name" => "My new product version",
                    "product" => "/products/".$this->productA2->getId()
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('detail', "name: Une version avec ce nom existe déjà\nproduct: Ce produit ne vous appartient pas")
        ;

        // As admin, on behalf of normal user

        $browser = $this->browser(actingAs: $this->admin)->post("/product_versions", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $productVersion = $browser->post("/product_versions", [
                "json" => [
                    "name" => "My all-new product version",
                    "product" => "/products/".$this->productA2->getId()
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
                "product",
                "name",
                "price",
                "visible",
                "rank",
                "createdAt",
                "updatedAt"
            ],
            array_keys($productVersion),
            "ProductVersion keys are not matching when posting as admin"
        );

        $browser->get($productVersion["@id"])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('product."@id"', "/products/".$this->productA2->getId())
        ;
    }

    public function testHardDeleteProductVersion(): void
    {
        // As guest

        $this->browser()
            ->delete("/product_versions/".$this->productVersions["A2"][1]->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user A

        $this->browser(actingAs: $this->userA)
            ->delete("/product_versions/".$this->productVersions["B1"][1]->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/product_versions/".$this->productVersions["A2"][1]->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/product_versions/".$this->productVersions["A2"][1]->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;

        // As normal user B

        $this->browser(actingAs: $this->userB)
            ->delete("/product_versions/".$this->productVersions["A3"][1]->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/product_versions/".$this->productVersions["B1"][1]->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/product_versions/".$this->productVersions["B1"][1]->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)

            ->delete("/product_versions/".$this->productVersions["B4"][1]->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;

        // As admin

        $this->browser(actingAs: $this->admin)
            ->delete("/product_versions/".$this->productVersions["A3"][1]->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/product_versions/".$this->productVersions["A3"][1]->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;
    }

    public function testPatchProductVersion(): void
    {
        // TODO: implement test
    }
}