<?php

namespace App\Repository;

use App\Entity\RestaurantMenu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RestaurantMenu>
 *
 * @method RestaurantMenu|null find($id, $lockMode = null, $lockVersion = null)
 * @method RestaurantMenu|null findOneBy(array $criteria, array $orderBy = null)
 * @method RestaurantMenu[]    findAll()
 * @method RestaurantMenu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RestaurantMenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RestaurantMenu::class);
    }

    public function save(RestaurantMenu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RestaurantMenu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return RestaurantMenu[] Returns an array of RestaurantMenu objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RestaurantMenu
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
