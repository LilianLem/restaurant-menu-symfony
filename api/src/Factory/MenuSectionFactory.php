<?php

namespace App\Factory;

use App\Entity\MenuSection;
use Zenstruck\Foundry\Persistence\RepositoryDecorator;

/**
 * @extends PersistentObjectFactory<MenuSection>
 *
 * @method        MenuSection                      create(array|callable $attributes = [])
 * @method static MenuSection                      createOne(array $attributes = [])
 * @method static MenuSection                      find(object|array|mixed $criteria)
 * @method static MenuSection                      findOrCreate(array $attributes)
 * @method static MenuSection                      first(string $sortedField = 'id')
 * @method static MenuSection                      last(string $sortedField = 'id')
 * @method static MenuSection                      random(array $attributes = [])
 * @method static MenuSection                      randomOrCreate(array $attributes = [])
 * @method static RepositoryDecorator<MenuSection> repository()
 * @method static MenuSection[]                    all()
 * @method static MenuSection[]                    createMany(int $number, array|callable $attributes = [])
 * @method static MenuSection[]                    createSequence(iterable|callable $sequence)
 * @method static MenuSection[]                    findBy(array $attributes)
 * @method static MenuSection[]                    randomRange(int $min, int $max, array $attributes = [])
 * @method static MenuSection[]                    randomSet(int $number, array $attributes = [])
 */
final class MenuSectionFactory extends PersistentObjectFactory
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
            // ->afterInstantiate(function(MenuSection $menuSection): void {})
        ;
    }

    public static function class(): string
    {
        return MenuSection::class;
    }
}
