<?php

namespace App\DataFixtures;

readonly class ProductData
{
    public function __construct(
        public string $name,
        public ?int $price,
        public ?string $description = null
    ) {

    }
}