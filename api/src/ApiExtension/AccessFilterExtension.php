<?php

namespace App\ApiExtension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Menu;
use App\Entity\Product;
use App\Entity\Restaurant;
use App\Entity\Section;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Override;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bundle\SecurityBundle\Security;

class AccessFilterExtension implements QueryCollectionExtensionInterface
{
    private const array COMPATIBLE_CLASSES = [
        Restaurant::class,
        Menu::class,
        Section::class,
        Product::class
    ];

    public function __construct(private Security $security)
    {
    }

    /** Prevents accessing entities owned by someone else in a collection (unless being an admin) */
    #[Override] public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if(!in_array($resourceClass, self::COMPATIBLE_CLASSES, true)) {
            return;
        }

        if(!$this->security->getUser()) {
            return;
        }

        if($this->security->isGranted("ROLE_ADMIN")) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $addNextInstructions = false;

        if($resourceClass === Product::class) {
            $queryBuilder->innerJoin(sprintf("%s.productSections", $rootAlias), "sectionProduct")
                ->innerJoin("sectionProduct.section", "section")
                ->innerJoin("section.sectionMenu", "menuSection")
            ;
            $addNextInstructions = true;
        } elseif($resourceClass === Section::class) {
            $queryBuilder->innerJoin(sprintf("%s.sectionMenu", $rootAlias), "menuSection");
            $addNextInstructions = true;
        }

        if($addNextInstructions) {
            $queryBuilder->innerJoin("menuSection.menu", "menu")
                ->innerJoin("menu.menuRestaurants", "restaurantMenu")
            ;
        } elseif($resourceClass === Menu::class) {
            $queryBuilder->innerJoin(sprintf("%s.menuRestaurants", $rootAlias), "restaurantMenu");
            $addNextInstructions = true;
        }

        if($addNextInstructions) {
            $queryBuilder->innerJoin("restaurantMenu.restaurant", "restaurant")
                ->andWhere("restaurant.owner = :owner")
            ;
        } elseif($resourceClass === Restaurant::class) {
            $queryBuilder->andWhere(sprintf("%s.owner = :owner", $rootAlias));
        } else {
            throw new Exception("Collection access filtering failed! This should never happen, please contact the developer.");
        }

        $queryBuilder->setParameter("owner", $this->security->getUser()->getId(), UlidType::NAME);
    }
}