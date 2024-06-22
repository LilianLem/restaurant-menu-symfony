<?php

namespace App\Factory;

use App\Entity\SectionProduct;
use App\Repository\SectionProductRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<SectionProduct>
 *
 * @method        SectionProduct|Proxy                     create(array|callable $attributes = [])
 * @method static SectionProduct|Proxy                     createOne(array $attributes = [])
 * @method static SectionProduct|Proxy                     find(object|array|mixed $criteria)
 * @method static SectionProduct|Proxy                     findOrCreate(array $attributes)
 * @method static SectionProduct|Proxy                     first(string $sortedField = 'id')
 * @method static SectionProduct|Proxy                     last(string $sortedField = 'id')
 * @method static SectionProduct|Proxy                     random(array $attributes = [])
 * @method static SectionProduct|Proxy                     randomOrCreate(array $attributes = [])
 * @method static SectionProductRepository|RepositoryProxy repository()
 * @method static SectionProduct[]|Proxy[]                 all()
 * @method static SectionProduct[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static SectionProduct[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static SectionProduct[]|Proxy[]                 findBy(array $attributes)
 * @method static SectionProduct[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static SectionProduct[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class SectionProductFactory extends ModelFactory
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
            // ->afterInstantiate(function(SectionProduct $sectionProduct): void {})
        ;
    }

    protected static function getClass(): string
    {
        return SectionProduct::class;
    }
}
