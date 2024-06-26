<?php

namespace App\Factory;

use App\DataFixtures\ProductData;
use App\DataFixtures\SectionProductsFixturesData;
use App\Entity\Product;
use Closure;
use Exception;
use JetBrains\PhpStorm\ExpectedValues;
use Psr\Log\LoggerInterface;
use Zenstruck\Foundry\Persistence\RepositoryDecorator;

/**
 * @extends PersistentObjectFactory<Product>
 *
 * @method        Product                      create(array|callable $attributes = [])
 * @method static Product                      createOne(array $attributes = [])
 * @method static Product                      find(object|array|mixed $criteria)
 * @method static Product                      findOrCreate(array $attributes)
 * @method static Product                      first(string $sortedField = 'id')
 * @method static Product                      last(string $sortedField = 'id')
 * @method static Product                      random(array $attributes = [])
 * @method static Product                      randomOrCreate(array $attributes = [])
 * @method static RepositoryDecorator<Product> repository()
 * @method static Product[]                    all()
 * @method static Product[]                    createMany(int $number, array|callable $attributes = [])
 * @method static Product[]                    createSequence(iterable|callable $sequence)
 * @method static Product[]                    findBy(array $attributes)
 * @method static Product[]                    randomRange(int $min, int $max, array $attributes = [])
 * @method static Product[]                    randomSet(int $number, array $attributes = [])
 */
final class ProductFactory extends PersistentObjectFactory
{
    /** @var Closure(): ProductData $productsTypeDefaultsGenerator */
    private static ?Closure $productsTypeDefaultsGenerator = null;

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct(private LoggerInterface $logger)
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        if(self::$productsTypeDefaultsGenerator) {
            return self::getProductsTypeDefaults();
        }

        return [
            'name' => self::faker()->sentence(),
            'description' => self::faker()->boolean(70) ? self::faker()->sentence(15, false) : null,
            'price' => self::faker()->randomPrice(200, 2000)
        ];
    }

    private static function getProductsTypeDefaults(): array
    {
        $productData = (self::$productsTypeDefaultsGenerator)();

        return [
            'name' => $productData->name,
            'description' => $productData->description,
            'price' => $productData->price
        ];
    }

    public function as(
        #[ExpectedValues(values: SectionProductsFixturesData::PRODUCTS_TYPES)] string $productsType,
        bool $unique = true
    ): static
    {
        if(!in_array($productsType, SectionProductsFixturesData::PRODUCTS_TYPES)) {
            throw new Exception("Ce type de produits n'existe pas !");
        }

        self::$productsTypeDefaultsGenerator = $unique ?
            fn() => self::faker()->unique()->$productsType() :
            fn() => self::faker()->$productsType()
        ;

        return $this;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Product $product): void {})
        ;
    }

    public static function class(): string
    {
        return Product::class;
    }
}
