<?php

namespace App\Factory;

use App\Entity\Menu;
use Zenstruck\Foundry\Persistence\RepositoryDecorator;

/**
 * @extends PersistentObjectFactory<Menu>
 *
 * @method        Menu                      create(array|callable $attributes = [])
 * @method static Menu                      createOne(array $attributes = [])
 * @method static Menu                      find(object|array|mixed $criteria)
 * @method static Menu                      findOrCreate(array $attributes)
 * @method static Menu                      first(string $sortedField = 'id')
 * @method static Menu                      last(string $sortedField = 'id')
 * @method static Menu                      random(array $attributes = [])
 * @method static Menu                      randomOrCreate(array $attributes = [])
 * @method static RepositoryDecorator<Menu> repository()
 * @method static Menu[]                    all()
 * @method static Menu[]                    createMany(int $number, array|callable $attributes = [])
 * @method static Menu[]                    createSequence(iterable|callable $sequence)
 * @method static Menu[]                    findBy(array $attributes)
 * @method static Menu[]                    randomRange(int $min, int $max, array $attributes = [])
 * @method static Menu[]                    randomSet(int $number, array $attributes = [])
 */
final class MenuFactory extends PersistentObjectFactory
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
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Menu $menu): void {})
        ;
    }

    public static function class(): string
    {
        return Menu::class;
    }
}
