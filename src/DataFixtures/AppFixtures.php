<?php

namespace App\DataFixtures;

use App\Entity\Allergen;
use App\Entity\Menu;
use App\Entity\Product;
use App\Entity\Restaurant;
use App\Entity\Section;
use App\Entity\User;
use App\Repository\AllergenRepository;
use App\Service\MenuService;
use App\Service\RestaurantService;
use App\Service\SectionService;
use Bezhanov\Faker\Provider\Commerce;
use Bluemmb\Faker\PicsumPhotosProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\Factory;
use Faker\Generator;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;
    private LoggerInterface $logger;
    private UserPasswordHasherInterface $passwordHasher;
    private MenuService $menuService;
    private RestaurantService $restaurantService;
    private SectionService $sectionService;

    /** @var Allergen[] $allergens */
    private array $allergens;

    /** @var User[] $users */
    private array $users = [];

    public function __construct(LoggerInterface $logger, UserPasswordHasherInterface $passwordHasher, MenuService $menuService, RestaurantService $restaurantService, SectionService $sectionService, AllergenRepository $allergenRepository)
    {
        $this->logger = $logger;
        $this->passwordHasher = $passwordHasher;
        $this->menuService = $menuService;
        $this->restaurantService = $restaurantService;
        $this->sectionService = $sectionService;
        $this->allergens = $allergenRepository->findAll();
        $this->faker = Factory::create("fr_FR");
        $this->faker->addProvider(new Commerce($this->faker));
        $this->faker->addProvider(new PicsumPhotosProvider($this->faker));
        $this->faker->addProvider(new RestaurantProvider($this->faker));
    }

    // TODO: product versions
    public function load(ObjectManager $manager): void
    {
        // --- User ---

        $adminUser = new User();
        $adminUser->setEmail("admin@restaurant-menu-symfony.tk")
            ->setRoles(["ROLE_ADMIN"])
            ->setPassword($this->passwordHasher->hashPassword($adminUser, "password"))
        ;
        $manager->persist($adminUser);

        for($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail("user$i@restaurant-menu-symfony.tk")
                ->setRoles(["ROLE_USER"])
                ->setPassword($this->passwordHasher->hashPassword($user, "password"))
                ->setVerified($this->faker->boolean(90))
            ;

            $manager->persist($user);
            $this->users[] = $user;
        }

        // --- Restaurant ---

        /** @var Restaurant[] $restaurants */
        $restaurants = [];

        for($i = 1; $i <= 15; $i++) {
            $restaurant = new Restaurant();
            $restaurant->setName($this->faker->company())
                ->setDescription($this->faker->boolean() ? $this->faker->sentence(12) : null)
                ->setVisible($this->faker->boolean(75))
                ->setLogo($this->faker->imageUrl(250, 100, true))
            ;

            $this->setRestaurantOwner($restaurant);

            //TODO: retravailler pour permettre la présence d'un produit dans plusieurs menus

            // --- Menu (à la carte) ---

            $aLaCarteStartersMenu = $this->createMenu(
                "Entrées",
                $restaurant,
                description: $this->faker->boolean() ? $this->faker->sentence(12) : null,
            );
            $this->createSectionInMenu(
                null,
                $aLaCarteStartersMenu,
                fn(): ProductData => $this->faker->starter(),
                mt_rand(3, 10),
                true
            );
            $manager->persist($aLaCarteStartersMenu);

            $aLaCarteDishesMenu = $this->createMenu(
                "Plats",
                $restaurant,
                description: $this->faker->boolean() ? $this->faker->sentence(12) : null
            );
            $this->createSectionInMenu(
                "Plats",
                $aLaCarteDishesMenu,
                fn(): ProductData => $this->faker->dish(),
                mt_rand(8, 16),
                true
            );
            $this->createSectionInMenu(
                "Accompagnement au choix",
                $aLaCarteDishesMenu,
                fn(): ProductData => $this->faker->sideDish(),
                mt_rand(3, 6)
            );
            $manager->persist($aLaCarteDishesMenu);

            $aLaCarteDessertsMenu = $this->createMenu(
                "Desserts",
                $restaurant,
                description: $this->faker->boolean() ? $this->faker->sentence(12) : null
            );
            $this->createSectionInMenu(
                null,
                $aLaCarteDessertsMenu,
                fn(): ProductData => $this->faker->dessert(),
                mt_rand(5, 10),
                true
            );
            $manager->persist($aLaCarteDessertsMenu);

            $softDrinksMenu = $this->createMenu(
                "Sans alcool",
                $restaurant,
                description: $this->faker->boolean() ? $this->faker->sentence(12) : null
            );
            $this->createSectionInMenu(
                "Boissons chaudes",
                $softDrinksMenu,
                fn(): ProductData => $this->faker->hotDrink(),
                mt_rand(8, 12)
            );
            $this->createSectionInMenu(
                "Boissons fraîches",
                $softDrinksMenu,
                fn(): ProductData => $this->faker->freshDrink(),
                mt_rand(10, 15)
            );
            $manager->persist($softDrinksMenu);

            $alcoholsMenu = $this->createMenu(
                "Alcool",
                $restaurant,
                description: $this->faker->boolean() ? $this->faker->sentence(12) : null
            );
            $this->createSectionInMenu(
                null,
                $alcoholsMenu,
                fn(): ProductData => $this->faker->alcoholicDrink(),
                mt_rand(10, 15)
            );
            $this->createSectionInMenu(
                "Cocktails",
                $alcoholsMenu,
                fn(): ProductData => $this->faker->alcoholicCocktail(),
                mt_rand(3, 8)
            );
            $manager->persist($alcoholsMenu);

            // --- Menu (combinations) ---

            // TODO : menus

            // ---

            $manager->persist($restaurant);
            $restaurants[] = $restaurant;
        }

        $manager->flush();
    }

    private function createMenu(
        string $name,
        Restaurant $restaurant,
        bool $isVisible = true,
        ?string $description = null,
        ?string $icon = null
    ): Menu
    {
        $menu = new Menu();
        $menu->setName($name)
            ->setVisible($isVisible)
            ->setDescription($description)
            ->setIcon($icon) // TODO: setup possible icons
        ;
        $this->restaurantService->addMenuToRestaurant($restaurant, $menu)->setVisible($isVisible);

        return $menu;
    }

    private function createSectionInMenu(
        ?string $name,
        Menu $menu,
        callable $productDataProvider,
        int $productsAmount = 10,
        bool $addRandomAllergens = false
    ): Section
    {
        if($productsAmount < 1) {
            throw new Exception("Erreur : 1 produit doit être généré au minimum");
        }

        $section = new Section();
        $section->setName($name);
        $this->menuService->addSectionToMenu($menu, $section)->setVisible(true);

        $this->createProducts($productDataProvider, $section, $productsAmount, addRandomAllergens: $addRandomAllergens);

        return $section;
    }

    /**
     * @param callable(): ProductData $productDataProvider
     * @return Product[]
     */
    private function createProducts(
        callable $productDataProvider,
        Section $section,
        int $amount = 10,
        int $maxRetries = 20,
        bool $addRandomAllergens = false,
        bool $throwErrorIfNotEnoughProducts = false
    ): array
    {
        if($amount < 1) {
            throw new Exception("Erreur : 1 produit doit être généré au minimum");
        }

        /** @var Product[] $products */
        $products = [];

        /** @var array<int, string> $productNamesCache */
        $productNamesCache = [];

        $productsAdded = 0;
        $retries = 0;
        while($productsAdded < $amount && $retries < $maxRetries) {
            $productData = $productDataProvider();

            if(in_array($productData->name, $productNamesCache)) {
                $retries++;
                continue;
            }

            $retries = 0;

            $product = new Product();
            $product->setName($productData->name)
                ->setDescription($productData->description)
                ->setPrice($productData->price)
            ;

            if($addRandomAllergens && $this->faker->boolean(80)) {
                $this->addProductAllergens($product);
            }

            $this->sectionService->addProductToSection($section, $product)
                ->setVisible($this->faker->boolean(80))
            ;
            $products[] = $product;

            $productNamesCache[] = $productData->name;
            $productsAdded++;
        }

        if($productsAdded < $amount) {
            $errorMessage = "Seuls $productsAdded ont été créés pour la section ".($section->getName() ?? "null")." du menu ".($section->getSectionMenu()?->getMenu()?->getName() ?? "null").(isset($products[0]) ? ". Exemple de nom de produit : ".$products[0]->getName() : "");

            if($throwErrorIfNotEnoughProducts) {
                throw new Exception("Erreur : $errorMessage");
            }

            $this->logger->warning($errorMessage);
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

    private function setRestaurantOwner(Restaurant $restaurant, int $maxRetries = 20): void
    {
        $retries = 0;
        while($retries < $maxRetries) {
            /** @var User $owner */
            $user = $this->faker->randomElement($this->users);

            if($user->getRestaurants()->exists(fn(int $key, Restaurant $r) => $r->getName() === $restaurant->getName())) {
                $retries++;
                continue;
            }

            $user->addRestaurant($restaurant);
            return;
        }

        throw new Exception("Erreur : impossible de trouver un propriétaire pour un restaurant nommé {$restaurant->getName()} ! Tous les utilisateurs essayés ont déjà un restaurant avec le même nom.");
    }
}
