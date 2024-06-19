<?php

namespace App\Entity\Auth;

use App\Entity\User;
use App\Repository\ResetPasswordRequestRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestTrait;

#[ORM\Entity(repositoryClass: ResetPasswordRequestRepository::class)]
class ResetPasswordRequest implements ResetPasswordRequestInterface
{
    use ResetPasswordRequestTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: "doctrine.ulid_generator")]
    private ?Ulid $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct(User $user, DateTimeInterface $expiresAt, string $selector, string $hashedToken)
    {
        $this->user = $user;
        $this->initialize($expiresAt, $selector, $hashedToken);
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
