<?php

namespace App\Factory;

use App\Entity\Section;
use Zenstruck\Foundry\Persistence\RepositoryDecorator;

/**
 * @extends PersistentObjectFactory<Section>
 *
 * @method        Section                      create(array|callable $attributes = [])
 * @method static Section                      createOne(array $attributes = [])
 * @method static Section                      find(object|array|mixed $criteria)
 * @method static Section                      findOrCreate(array $attributes)
 * @method static Section                      first(string $sortedField = 'id')
 * @method static Section                      last(string $sortedField = 'id')
 * @method static Section                      random(array $attributes = [])
 * @method static Section                      randomOrCreate(array $attributes = [])
 * @method static RepositoryDecorator<Section> repository()
 * @method static Section[]                    all()
 * @method static Section[]                    createMany(int $number, array|callable $attributes = [])
 * @method static Section[]                    createSequence(iterable|callable $sequence)
 * @method static Section[]                    findBy(array $attributes)
 * @method static Section[]                    randomRange(int $min, int $max, array $attributes = [])
 * @method static Section[]                    randomSet(int $number, array $attributes = [])
 */
final class SectionFactory extends PersistentObjectFactory
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
            'name' => self::faker()->boolean(10) ? self::faker()->sentence(3, false) : null
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Section $section): void {})
        ;
    }

    public static function class(): string
    {
        return Section::class;
    }
}
