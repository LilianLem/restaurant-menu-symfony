<?php

namespace App\Factory;

use App\Entity\SectionProduct;
use Zenstruck\Foundry\Persistence\RepositoryDecorator;

/**
 * @extends PersistentObjectFactory<SectionProduct>
 *
 * @method        SectionProduct                      create(array|callable $attributes = [])
 * @method static SectionProduct                      createOne(array $attributes = [])
 * @method static SectionProduct                      find(object|array|mixed $criteria)
 * @method static SectionProduct                      findOrCreate(array $attributes)
 * @method static SectionProduct                      first(string $sortedField = 'id')
 * @method static SectionProduct                      last(string $sortedField = 'id')
 * @method static SectionProduct                      random(array $attributes = [])
 * @method static SectionProduct                      randomOrCreate(array $attributes = [])
 * @method static RepositoryDecorator<SectionProduct> repository()
 * @method static SectionProduct[]                    all()
 * @method static SectionProduct[]                    createMany(int $number, array|callable $attributes = [])
 * @method static SectionProduct[]                    createSequence(iterable|callable $sequence)
 * @method static SectionProduct[]                    findBy(array $attributes)
 * @method static SectionProduct[]                    randomRange(int $min, int $max, array $attributes = [])
 * @method static SectionProduct[]                    randomSet(int $number, array $attributes = [])
 */
final class SectionProductFactory extends PersistentObjectFactory
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
            // ->afterInstantiate(function(SectionProduct $sectionProduct): void {})
        ;
    }

    public static function class(): string
    {
        return SectionProduct::class;
    }
}
