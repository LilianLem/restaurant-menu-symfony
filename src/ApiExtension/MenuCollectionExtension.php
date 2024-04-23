<?php

namespace App\ApiExtension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Menu;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bundle\SecurityBundle\Security;

class MenuCollectionExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private Security $security)
    {
    }

    #[Override] public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if(Menu::class !== $resourceClass) {
            return;
        }

        if(!$this->security->getUser()) {
            return;
        }

        if($this->security->isGranted("ROLE_ADMIN")) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->innerJoin(sprintf("%s.menuRestaurants", $rootAlias), "restaurantMenu")
            ->innerJoin("restaurantMenu.restaurant", "restaurant")
            ->andWhere("restaurant.owner = :owner")
            ->setParameter("owner", $this->security->getUser()->getId(), UlidType::NAME)
        ;
    }
}