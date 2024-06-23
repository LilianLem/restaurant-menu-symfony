<?php

namespace App\Factory;

use Faker\Generator;

abstract class PersistentObjectFactory extends \Zenstruck\Foundry\Persistence\PersistentObjectFactory
{
    final public static function getFaker(): Generator
    {
        return static::faker();
    }
}