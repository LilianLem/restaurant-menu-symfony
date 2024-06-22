<?php

namespace App\Factory;

use App\Entity\Restaurant;
use App\Repository\RestaurantRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Restaurant>
 *
 * @method        Restaurant|Proxy                     create(array|callable $attributes = [])
 * @method static Restaurant|Proxy                     createOne(array $attributes = [])
 * @method static Restaurant|Proxy                     find(object|array|mixed $criteria)
 * @method static Restaurant|Proxy                     findOrCreate(array $attributes)
 * @method static Restaurant|Proxy                     first(string $sortedField = 'id')
 * @method static Restaurant|Proxy                     last(string $sortedField = 'id')
 * @method static Restaurant|Proxy                     random(array $attributes = [])
 * @method static Restaurant|Proxy                     randomOrCreate(array $attributes = [])
 * @method static RestaurantRepository|RepositoryProxy repository()
 * @method static Restaurant[]|Proxy[]                 all()
 * @method static Restaurant[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Restaurant[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Restaurant[]|Proxy[]                 findBy(array $attributes)
 * @method static Restaurant[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Restaurant[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class RestaurantFactory extends ModelFactory
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
    protected function getDefaults(): array
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
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Restaurant $restaurant): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Restaurant::class;
    }
}
