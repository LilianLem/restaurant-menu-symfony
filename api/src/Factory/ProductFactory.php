<?php

namespace App\Factory;

use App\DataFixtures\ProductData;
use App\DataFixtures\SectionProductsFixturesData;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Closure;
use Exception;
use JetBrains\PhpStorm\ExpectedValues;
use Psr\Log\LoggerInterface;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Product>
 *
 * @method        Product|Proxy                     create(array|callable $attributes = [])
 * @method static Product|Proxy                     createOne(array $attributes = [])
 * @method static Product|Proxy                     find(object|array|mixed $criteria)
 * @method static Product|Proxy                     findOrCreate(array $attributes)
 * @method static Product|Proxy                     first(string $sortedField = 'id')
 * @method static Product|Proxy                     last(string $sortedField = 'id')
 * @method static Product|Proxy                     random(array $attributes = [])
 * @method static Product|Proxy                     randomOrCreate(array $attributes = [])
 * @method static ProductRepository|RepositoryProxy repository()
 * @method static Product[]|Proxy[]                 all()
 * @method static Product[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Product[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Product[]|Proxy[]                 findBy(array $attributes)
 * @method static Product[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Product[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class ProductFactory extends ModelFactory
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
    protected function getDefaults(): array
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
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Product $product): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Product::class;
    }
}
