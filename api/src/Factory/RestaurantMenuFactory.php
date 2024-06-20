<?php

namespace App\Factory;

use App\Entity\RestaurantMenu;
use App\Repository\RestaurantMenuRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<RestaurantMenu>
 *
 * @method        RestaurantMenu|Proxy                     create(array|callable $attributes = [])
 * @method static RestaurantMenu|Proxy                     createOne(array $attributes = [])
 * @method static RestaurantMenu|Proxy                     find(object|array|mixed $criteria)
 * @method static RestaurantMenu|Proxy                     findOrCreate(array $attributes)
 * @method static RestaurantMenu|Proxy                     first(string $sortedField = 'id')
 * @method static RestaurantMenu|Proxy                     last(string $sortedField = 'id')
 * @method static RestaurantMenu|Proxy                     random(array $attributes = [])
 * @method static RestaurantMenu|Proxy                     randomOrCreate(array $attributes = [])
 * @method static RestaurantMenuRepository|RepositoryProxy repository()
 * @method static RestaurantMenu[]|Proxy[]                 all()
 * @method static RestaurantMenu[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static RestaurantMenu[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static RestaurantMenu[]|Proxy[]                 findBy(array $attributes)
 * @method static RestaurantMenu[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static RestaurantMenu[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class RestaurantMenuFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Other values than visible can't have defaults because they need to be set in their own restaurant context
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function getDefaults(): array
    {
        return [
            'visible' => self::faker()->boolean(80),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(RestaurantMenu $restaurantMenu): void {})
        ;
    }

    protected static function getClass(): string
    {
        return RestaurantMenu::class;
    }
}
