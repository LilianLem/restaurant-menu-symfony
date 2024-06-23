<?php

namespace App\Factory;

use Faker\Generator;

abstract class PersistentObjectFactory extends \Zenstruck\Foundry\Persistence\PersistentObjectFactory
{
    /** Needed to make generators unique in fixtures (base method became protected in latest version) */
    final public static function getFaker(): Generator
    {
        return static::faker();
    }
}