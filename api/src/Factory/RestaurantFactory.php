<?php

namespace App\Factory;

use App\Entity\Restaurant;
use Zenstruck\Foundry\Persistence\RepositoryDecorator;

/**
 * @extends PersistentObjectFactory<Restaurant>
 *
 * @method        Restaurant                      create(array|callable $attributes = [])
 * @method static Restaurant                      createOne(array $attributes = [])
 * @method static Restaurant                      find(object|array|mixed $criteria)
 * @method static Restaurant                      findOrCreate(array $attributes)
 * @method static Restaurant                      first(string $sortedField = 'id')
 * @method static Restaurant                      last(string $sortedField = 'id')
 * @method static Restaurant                      random(array $attributes = [])
 * @method static Restaurant                      randomOrCreate(array $attributes = [])
 * @method static RepositoryDecorator<Restaurant> repository()
 * @method static Restaurant[]                    all()
 * @method static Restaurant[]                    createMany(int $number, array|callable $attributes = [])
 * @method static Restaurant[]                    createSequence(iterable|callable $sequence)
 * @method static Restaurant[]                    findBy(array $attributes)
 * @method static Restaurant[]                    randomRange(int $min, int $max, array $attributes = [])
 * @method static Restaurant[]                    randomSet(int $number, array $attributes = [])
 */
final class RestaurantFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Warning: a user CANNOT have two restaurants with the same name!
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'inTrash' => self::faker()->boolean(10),
            'name' => self::faker()->company(),
            'description' => self::faker()->boolean() ? self::faker()->sentence(12) : null,
            'visible' => self::faker()->boolean(75),
            'logo' => self::faker()->imageUrl(250, 100, true),
            'owner' => UserFactory::randomNormalUser(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Restaurant $restaurant): void {})
        ;
    }

    public static function class(): string
    {
        return Restaurant::class;
    }
}
