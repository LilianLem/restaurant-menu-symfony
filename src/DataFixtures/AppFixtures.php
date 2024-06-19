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
use Faker\Factory;
use Faker\Generator;
use JetBrains\PhpStorm\ExpectedValues;
use Psr\Log\LoggerInterface;
use Zenstruck\Foundry\Proxy;

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

    private readonly ObjectManager $manager;

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
                ["data" => new SectionProductsFixturesData("hotDrink", 6, 10, false), "name" => "Boissons chaudes"],
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
        $this->manager = $manager;

        UserFactory::new()
            ->asSuperAdmin()
            ->create()
        ;

        UserFactory::new()
            ->asAdmin()
            ->create()
        ;

        /** @var array<Proxy<User>> $normalUsers */
        $normalUsers = UserFactory::createMany(20);

        $usersWithRestaurants = 0;
        foreach($normalUsers as $user) {
            if($this->faker->boolean() || $usersWithRestaurants >= 10) {
                continue;
            }

            $this->generateRestaurants($user);
            $usersWithRestaurants++;
        }

        $manager->flush();
    }

    /** @param Proxy<User> $user */
    private function generateRestaurants(Proxy $user): void
    {
        RestaurantFactory::faker()->unique(true);
        $restaurants = RestaurantFactory::createMany(mt_rand(1, 3), fn() => [
            "name" => RestaurantFactory::faker()->unique()->company(),
            "owner" => $user
        ]);

        $this->manager->flush();

        $this->generateMenusWithStrategy(
            $restaurants,
            $this->faker->boolean() ? "distinct" : "shared"
        );
    }

    /** @param array<Proxy<Restaurant>> $restaurants */
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
            /** @var array<Proxy<Menu>> $menus */
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

    /** @return array<Proxy<Menu>> */
    private function generateMenus(): array
    {
        /** @var array<Proxy<Menu>> $menus */
        $menus = [];

        foreach($this->menus as $name => $sectionsInfo) {
            $menu = MenuFactory::createOne([
                "name" => $name
            ]);
            $menus[] = $menu;

            $sectionRank = 1;
            foreach($sectionsInfo as $sectionInfo) {
                $section = SectionFactory::createOne([
                    "name" => $sectionInfo["name"] ?? null,
                    "productsFixturesData" => $sectionInfo["data"]
                ]);

                MenuSectionFactory::createOne([
                    "menu" => $menu,
                    "section" => $section,
                    "rank" => $sectionRank++
                ]);
            }

            $this->manager->flush();
        }

        return $menus;
    }

    /** @param array<Proxy<Restaurant>> $restaurants */
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

            /** @var Menu[] $menus */
            $menus = $restaurant->getRestaurantMenus()->map(fn(RestaurantMenu $rMenu):Menu => $rMenu->getMenu());

            foreach($menus as $menu) {
                /** @var Section[] $sections */
                $sections = $menu->getMenuSections()->map(fn(MenuSection $mSection): Section => $mSection->getSection());

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

            $this->manager->flush();
        }
    }

    /**
     * @param SectionProductsFixturesData[] $productsFixturesDataArray
     * @return array<string, array<Proxy<Product>>>
     */
    private function generateProducts(array $productsFixturesDataArray = []): array
    {
        if(!$productsFixturesDataArray) {
            foreach($this->menus as $menuSections) {
                array_push(
                    $productsFixturesDataArray,
                    ...array_map(
                        fn($section): SectionProductsFixturesData => $section["data"],
                        $menuSections
                    )
                );
            }
        }

        /** @var array<string, array<Proxy<Product>>> $products */
        $products = [];

        foreach($productsFixturesDataArray as $productsFixturesData){
            ProductFactory::faker()->unique(true);
            $currentProducts = ProductFactory::new()
                ->as($productsFixturesData->productsType)
                ->createMany(
                    mt_rand($productsFixturesData->minProducts, $productsFixturesData->maxProducts)
                )
            ;

            if($productsFixturesData->addAllergens) {
                foreach($currentProducts as $product) {
                    $this->addProductAllergens($product);
                }
            }

            $products[$productsFixturesData->productsType] = $currentProducts;
        }

        return $products;
    }

    /** @param Proxy<Product> $product */
    private function addProductAllergens(Proxy $product): void
    {
        $allergens = $this->faker->randomElements($this->allergens, mt_rand(1, 5));

        foreach($allergens as $allergen) {
            $product->addAllergen($allergen);
        }
    }
}
