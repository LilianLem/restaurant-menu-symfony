<?php

namespace App\DataFixtures;

use Exception;
use JetBrains\PhpStorm\ExpectedValues;

class SectionProductsFixturesData
{
    /** @var string[] */
    public const array PRODUCTS_TYPES = ["starter","dish","sideDish","dessert","hotDrink","freshDrink","alcoholicDrink","alcoholicCocktail"];

    public function __construct(
        #[ExpectedValues(values: self::PRODUCTS_TYPES)] public readonly string $productsType,
        public readonly int $minProducts,
        public readonly int $maxProducts,
        public readonly bool $addAllergens = true
    )
    {
        if(!in_array($this->productsType, self::PRODUCTS_TYPES)) {
            throw new Exception("Ce type de produits n'existe pas !");
        }
    }
}