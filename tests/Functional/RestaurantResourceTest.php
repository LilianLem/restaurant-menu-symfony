<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class RestaurantResourceTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;

    public function testGetCollectionOfRestaurants(): void
    {
        $this->browser()
            ->get("/api/restaurants")
            ->assertJson()
            ->assertJsonMatches('"hydra:totalItems"', 0)
        ;
    }
}