<?php

namespace App\Factory;

use App\Entity\MenuSection;
use App\Repository\MenuSectionRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<MenuSection>
 *
 * @method        MenuSection|Proxy                     create(array|callable $attributes = [])
 * @method static MenuSection|Proxy                     createOne(array $attributes = [])
 * @method static MenuSection|Proxy                     find(object|array|mixed $criteria)
 * @method static MenuSection|Proxy                     findOrCreate(array $attributes)
 * @method static MenuSection|Proxy                     first(string $sortedField = 'id')
 * @method static MenuSection|Proxy                     last(string $sortedField = 'id')
 * @method static MenuSection|Proxy                     random(array $attributes = [])
 * @method static MenuSection|Proxy                     randomOrCreate(array $attributes = [])
 * @method static MenuSectionRepository|RepositoryProxy repository()
 * @method static MenuSection[]|Proxy[]                 all()
 * @method static MenuSection[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static MenuSection[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static MenuSection[]|Proxy[]                 findBy(array $attributes)
 * @method static MenuSection[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static MenuSection[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class MenuSectionFactory extends ModelFactory
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
            // ->afterInstantiate(function(MenuSection $menuSection): void {})
        ;
    }

    protected static function getClass(): string
    {
        return MenuSection::class;
    }
}
