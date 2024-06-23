<?php

namespace App\Factory;

use App\Entity\RestaurantMenu;
use Zenstruck\Foundry\Persistence\RepositoryDecorator;

/**
 * @extends PersistentObjectFactory<RestaurantMenu>
 *
 * @method        RestaurantMenu                      create(array|callable $attributes = [])
 * @method static RestaurantMenu                      createOne(array $attributes = [])
 * @method static RestaurantMenu                      find(object|array|mixed $criteria)
 * @method static RestaurantMenu                      findOrCreate(array $attributes)
 * @method static RestaurantMenu                      first(string $sortedField = 'id')
 * @method static RestaurantMenu                      last(string $sortedField = 'id')
 * @method static RestaurantMenu                      random(array $attributes = [])
 * @method static RestaurantMenu                      randomOrCreate(array $attributes = [])
 * @method static RepositoryDecorator<RestaurantMenu> repository()
 * @method static RestaurantMenu[]                    all()
 * @method static RestaurantMenu[]                    createMany(int $number, array|callable $attributes = [])
 * @method static RestaurantMenu[]                    createSequence(iterable|callable $sequence)
 * @method static RestaurantMenu[]                    findBy(array $attributes)
 * @method static RestaurantMenu[]                    randomRange(int $min, int $max, array $attributes = [])
 * @method static RestaurantMenu[]                    randomSet(int $number, array $attributes = [])
 */
final class RestaurantMenuFactory extends PersistentObjectFactory
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
    protected function defaults(): array|callable
    {
        return [
            'visible' => self::faker()->boolean(80),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(RestaurantMenu $restaurantMenu): void {})
        ;
    }

    public static function class(): string
    {
        return RestaurantMenu::class;
    }
}
