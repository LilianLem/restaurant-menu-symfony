<?php

namespace App\DataFixtures;

use App\Entity\Allergen;
use App\Entity\Menu;
use App\Entity\MenuSection;
use App\Entity\Product;
use App\Entity\Restaurant;
use App\Entity\RestaurantMenu;
use App\Entity\Section;
use App\Entity\User;
use App\Factory\MenuFactory;
use App\Factory\MenuSectionFactory;
use App\Factory\ProductFactory;
use App\Factory\RestaurantFactory;
use App\Factory\RestaurantMenuFactory;
use App\Factory\SectionFactory;
use App\Factory\SectionProductFactory;
use App\Factory\UserFactory;
use App\Repository\AllergenRepository;
use Bezhanov\Faker\Provider\Commerce;
use Bluemmb\Faker\PicsumPhotosProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Factory;
use Faker\Generator;
use JetBrains\PhpStorm\ExpectedValues;
use Psr\Log\LoggerInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;

    /** @var Allergen[] $allergens */
    private array $allergens;

    /** @var array<string, array{
     *     data: SectionProductsFixturesData,
     *     name: string|null
     *  }[]> $menus
     */
    private readonly array $menus;

    public function __construct(
        private LoggerInterface $logger,
        AllergenRepository $allergenRepository
    )
    {
        $this->allergens = $allergenRepository->findAll();
        $this->faker = Factory::create("fr_FR");
        $this->faker->addProvider(new Commerce($this->faker));
        $this->faker->addProvider(new PicsumPhotosProvider($this->faker));
        $this->faker->addProvider(new ProductProvider($this->faker));
        $this->menus = [
            "Entrées" => [["data" => new SectionProductsFixturesData("starter", 3, 10)]],
            "Plats" => [["data" => new SectionProductsFixturesData("dish", 8, 16)]],
            "Accompagnement au choix" => [["data" => new SectionProductsFixturesData("sideDish", 3, 6)]],
            "Desserts" => [["data" => new SectionProductsFixturesData("dessert", 5, 10)]],
            "Sans alcool" => [
                ["data" => new SectionProductsFixturesData("hotDrink", 8, 12, false), "name" => "Boissons chaudes"],
                ["data" => new SectionProductsFixturesData("freshDrink", 10, 15, false), "name" => "Boissons fraîches"]
            ],
            "Alcool" => [
                ["data" => new SectionProductsFixturesData("alcoholicDrink", 10, 15, false)],
                ["data" => new SectionProductsFixturesData("alcoholicCocktail", 3, 8, false), "name" => "Cocktails"]
            ]
        ];
    }

    // TODO: product versions
    public function load(ObjectManager $manager): void
    {
        UserFactory::new()
            ->asSuperAdmin()
            ->create()
        ;

        UserFactory::new()
            ->asAdmin()
            ->create()
        ;

        UserFactory::createMany(20);

        foreach(UserFactory::all() as $user) {
            if($this->faker->boolean(10)) {
                continue;
            }

            $this->generateRestaurants($user);
        }

        $manager->flush();
    }

    private function generateRestaurants(User $user): void
    {
        RestaurantFactory::faker()->unique(true);
        $restaurants = RestaurantFactory::createMany(mt_rand(1, 3), fn() => [
            "name" => RestaurantFactory::faker()->unique()->company(),
            "owner" => $user
        ]);

        $this->generateMenusWithStrategy(
            $restaurants,
            $this->faker->boolean(70) ? "distinct" : "shared"
        );
    }

    /** @param Restaurant[] $restaurants */
    private function generateMenusWithStrategy(
        array $restaurants,
        #[ExpectedValues(values: ["distinct", "shared"])] string $strategy
    ): void
    {
        if($strategy === "shared") {
            $menus = $this->generateMenus();
        }

        foreach($restaurants as $restaurant) {
            if($strategy === "distinct") {
                $menus = $this->generateMenus();
            }

            $menuRank = 1;
            /** @var Menu[] $menus */
            foreach($menus as $menu) {
                RestaurantMenuFactory::createOne([
                    "restaurant" => $restaurant,
                    "menu" => $menu,
                    "rank" => $menuRank++
                ]);
            }
        }

        $productsStrategy = match(true) {
            count($restaurants) === 1 => "fullySharedMenus",
            $strategy === "shared" => "fullySharedMenus",
            default => $this->faker->boolean() ? "partiallyShared" : "distinct"
        };

        $this->generateProductsInRestaurantsWithStrategy(
            $restaurants,
            $productsStrategy
        );
    }

    /** @return Menu[] */
    private function generateMenus(): array
    {
        $menus = [];

        $menuRank = 1;
        foreach($this->menus as $name => $sectionsInfo) {
            $sectionRank = 1;
            // TODO: possible not working increment
            $menuSections = MenuSectionFactory::createMany(count($sectionsInfo), fn() => ["rank" => $sectionRank++]);

            foreach($menuSections as $mSection) {
                $sectionInfo = current($sectionsInfo);
                SectionFactory::createOne([
                    "name" => $sectionInfo["name"] ?? null,
                    "productsFixturesData" => $sectionInfo["data"],
                    "sectionMenu" => $mSection
                ]);

                next($sectionsInfo);
            }

            $menus[] = MenuFactory::createOne([
                "menuRestaurants" => [RestaurantMenuFactory::createOne([
                    "rank" => $menuRank++
                ])],
                "name" => $name,
                "menuSections" => $menuSections
            ]);
        }

        return $menus;
    }

    /** @param Restaurant[] $restaurants */
    private function generateProductsInRestaurantsWithStrategy(
        array $restaurants,
        #[ExpectedValues(values: ["fullySharedMenus", "partiallyShared", "distinct"])] string $strategy
    ): void
    {
        if($strategy === "fullySharedMenus") {
            $restaurants = [$restaurants[0]];
            $products = $this->generateProducts();
        } elseif($strategy === "partiallyShared") {
            /** @var array<int, int> $restaurantGroups */
            $restaurantGroups = [];
            $arraySeparator = mt_rand(1, count($restaurants) - 1);

            foreach(array_keys($restaurants) as $key) {
                $restaurantGroups[$key] = $key < $arraySeparator ? 0 : 1;
            }

            $productsByGroup = [
                0 => $this->generateProducts(),
                1 => $this->generateProducts()
            ];
        }

        foreach($restaurants as $key => $restaurant) {
            if($strategy === "distinct") {
                $products = $this->generateProducts();
            } elseif($strategy === "partiallyShared") {
                $products = $productsByGroup[$restaurantGroups[$key]];
            }

            /**
             * @var Menu[] $menus
             * @var RestaurantMenu $rMenu
             */
            $menus = $restaurant->getRestaurantMenus()->map(fn($rMenu) => $rMenu->getMenu());

            foreach($menus as $menu) {
                /**
                 * @var Section[] $sections
                 * @var MenuSection $mSection
                 */
                $sections = $menu->getMenuSections()->map(fn($mSection) => $mSection->getSection());

                foreach($sections as $section) {
                    $rank = 1;
                    foreach($products[$section->getProductsFixturesData()->productsType] as $product) {
                        SectionProductFactory::createOne([
                            "section" => $section,
                            "product" => $product,
                            "rank" => $rank++
                        ]);
                    }
                }
            }
        }
    }

    /**
     * @param SectionProductsFixturesData[] $productsFixturesDataArray
     * @return array<string, Product[]>
     */
    private function generateProducts(array $productsFixturesDataArray = []): array
    {
        if(!$productsFixturesDataArray) {
            foreach($this->menus as $menuSections) {
                array_push($productsFixturesDataArray, ...array_map(fn($section) => $section["data"], $menuSections));
            }
        }

        /** @var array<string, Product[]> $products */
        $products = [];

        foreach($productsFixturesDataArray as $productsFixturesData){
            ProductFactory::faker()->unique(true);
            $currentProducts = ProductFactory::new()->as($productsFixturesData->productsType)->createMany(mt_rand($productsFixturesData->minProducts, $productsFixturesData->maxProducts));

            if($productsFixturesData->addAllergens) {
                foreach($currentProducts as $product) {
                    $this->addProductAllergens($product);
                }
            }

            $products[$productsFixturesData->productsType] = $currentProducts;
        }

        return $products;
    }

    private function addProductAllergens(Product $product): void
    {
        $allergens = $this->faker->randomElements($this->allergens, mt_rand(1, 5));

        foreach($allergens as $allergen) {
            $product->addAllergen($allergen);
        }
    }
}
