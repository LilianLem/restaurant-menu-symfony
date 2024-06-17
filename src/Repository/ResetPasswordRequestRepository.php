<?php

namespace App\Repository;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UlidType;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Persistence\Repository\ResetPasswordRequestRepositoryTrait;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;

/**
 * @extends ServiceEntityRepository<ResetPasswordRequest>
 */
class ResetPasswordRequestRepository extends ServiceEntityRepository implements ResetPasswordRequestRepositoryInterface
{
    use ResetPasswordRequestRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    /**
     * @param User $user
     */
    public function createResetPasswordRequest(object $user, DateTimeInterface $expiresAt, string $selector, string $hashedToken): ResetPasswordRequestInterface
    {
        return new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);
    }

    // Overrides trait method to be compatible with ID Ulid type
    public function getMostRecentNonExpiredRequestDate(object $user): ?DateTimeInterface
    {
        // Normally there is only 1 max request per use, but written to be flexible
        /** @var ResetPasswordRequestInterface $resetPasswordRequest */
        $resetPasswordRequest = $this->createQueryBuilder("t")
            ->where("t.user = :user")
            ->setParameter("user", $user->getId(), UlidType::NAME)
            ->orderBy("t.requestedAt", 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null !== $resetPasswordRequest && !$resetPasswordRequest->isExpired()) {
            return $resetPasswordRequest->getRequestedAt();
        }

        return null;
    }

    // Overrides trait method to be compatible with ID Ulid type
    public function removeResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $this->createQueryBuilder("t")
            ->delete()
            ->where("t.user = :user")
            ->setParameter("user", $resetPasswordRequest->getUser()->getId(), UlidType::NAME)
            ->getQuery()
            ->execute()
        ;
    }

    // Overrides trait method to be compatible with ID Ulid type
    /**
     * @param User $user
     */
    public function removeRequests(object $user): void
    {
        $query = $this->createQueryBuilder("t")
            ->delete()
            ->where("t.user = :user")
            ->setParameter("user", $user->getId(), UlidType::NAME)
        ;

        $query->getQuery()->execute();
    }
}
