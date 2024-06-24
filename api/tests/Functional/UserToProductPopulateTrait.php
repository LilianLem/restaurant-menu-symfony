<?php

namespace App\Tests\Functional;

use App\Entity\Product;
use App\Entity\Section;
use App\Factory\ProductFactory;
use App\Factory\SectionProductFactory;
use Carbon\Carbon;

trait UserToProductPopulateTrait
{
    use UserToSectionPopulateTrait {
        populate as parentPopulate;
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

    private function populate(): void
    {
        $this->parentPopulate();

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
}