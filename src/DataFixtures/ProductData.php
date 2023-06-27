<?php

namespace App\DataFixtures;

class ProductData
{
    public function __construct(
        public readonly string $name,
        public readonly ?int $price,
        public readonly ?string $description = null
    ) {

    }
}