<?php

namespace App\Factory;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Menu>
 *
 * @method        Menu|Proxy                     create(array|callable $attributes = [])
 * @method static Menu|Proxy                     createOne(array $attributes = [])
 * @method static Menu|Proxy                     find(object|array|mixed $criteria)
 * @method static Menu|Proxy                     findOrCreate(array $attributes)
 * @method static Menu|Proxy                     first(string $sortedField = 'id')
 * @method static Menu|Proxy                     last(string $sortedField = 'id')
 * @method static Menu|Proxy                     random(array $attributes = [])
 * @method static Menu|Proxy                     randomOrCreate(array $attributes = [])
 * @method static MenuRepository|RepositoryProxy repository()
 * @method static Menu[]|Proxy[]                 all()
 * @method static Menu[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Menu[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Menu[]|Proxy[]                 findBy(array $attributes)
 * @method static Menu[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Menu[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class MenuFactory extends ModelFactory
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
    protected function getDefaults(): array
    {
        // TODO: setup possible icons
        return [
            'inTrash' => false,
            'name' => self::faker()->sentence(3, false),
            'description' => self::faker()->boolean() ? self::faker()->sentence(12) : null
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Menu $menu): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Menu::class;
    }
}
