<?php

namespace App\ApiExtension;

use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Override;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bundle\SecurityBundle\Security;

class UserAdminRestrictExtension implements QueryItemExtensionInterface
{
    public function __construct(private Security $security)
    {
    }

    /** Prevents "simple" admins from editing other admins in PATCH or DELETE operations */
    #[Override] public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        if(User::class !== $resourceClass) {
            return;
        }

        if(!$this->security->getUser()) {
            return;
        }

        if($operation instanceof Get || $operation instanceof Post) {
            return;
        }

        if(
            !$this->security->isGranted("ROLE_ADMIN") || /* Security on User entity already prevents editing other users */
            $this->security->isGranted("ROLE_SUPER_ADMIN")) /* Super-admin can edit everyone */ {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->andWhere(sprintf('%1$s.id = :user OR (%1$s.roles NOT LIKE :adminRole AND %1$s.roles NOT LIKE :sAdminRole)', $rootAlias))
            ->setParameter("user", $this->security->getUser()->getId(), UlidType::NAME)
            ->setParameter("adminRole", '%"ROLE_ADMIN"%')
            ->setParameter("sAdminRole", '%"ROLE_SUPER_ADMIN"%')
        ;
    }
}