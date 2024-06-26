<?php

namespace App\Factory;

use App\Entity\ProductVersion;
use Zenstruck\Foundry\Persistence\RepositoryDecorator;

/**
 * @extends PersistentObjectFactory<ProductVersion>
 *
 * @method        ProductVersion                      create(array|callable $attributes = [])
 * @method static ProductVersion                      createOne(array $attributes = [])
 * @method static ProductVersion                      find(object|array|mixed $criteria)
 * @method static ProductVersion                      findOrCreate(array $attributes)
 * @method static ProductVersion                      first(string $sortedField = 'id')
 * @method static ProductVersion                      last(string $sortedField = 'id')
 * @method static ProductVersion                      random(array $attributes = [])
 * @method static ProductVersion                      randomOrCreate(array $attributes = [])
 * @method static RepositoryDecorator<ProductVersion> repository()
 * @method static ProductVersion[]                    all()
 * @method static ProductVersion[]                    createMany(int $number, array|callable $attributes = [])
 * @method static ProductVersion[]                    createSequence(iterable|callable $sequence)
 * @method static ProductVersion[]                    findBy(array $attributes)
 * @method static ProductVersion[]                    randomRange(int $min, int $max, array $attributes = [])
 * @method static ProductVersion[]                    randomSet(int $number, array $attributes = [])
 */
final class ProductVersionFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->sentence(3, false)
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(ProductVersion $version): void {})
            ;
    }

    public static function class(): string
    {
        return ProductVersion::class;
    }
}
