<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\UserRepository;
use App\Security\ApiSecurityExpressionDirectory;
use App\State\UserHashPasswordProcessor;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity("email", message: "Une erreur est survenue dans la création du compte. Si vous en avez déjà un, cliquez sur Connexion")]
#[ApiResource(
    operations: [
        new GetCollection(
            security: ApiSecurityExpressionDirectory::ADMIN_ONLY
        ),
        new Get(
            normalizationContext: ["groups" => ["user:read", "user:read:get", "restaurant:read"]],
            security: 'is_granted("ROLE_ADMIN") or object === user'
        ),
        new Post(
            denormalizationContext: ["groups" => ["user:write", "user:write:post"]],
            security: 'is_granted("ROLE_ADMIN") or not(is_granted("ROLE_USER"))', // Prevents a new registration when already connected (except when admin)
            validationContext: ["groups" => ["Default", "postValidation"]],
            processor: UserHashPasswordProcessor::class
        ), // TODO: handle user registration
        new Delete(
            security: 'is_granted("ROLE_ADMIN") and object !== user' // TODO: extra security to prevent deleting by mistake (strong auth + user confirmation)
        ),
        new Patch(
            denormalizationContext: ["groups" => ["user:write", "user:write:update"]],
            security: 'is_granted("ROLE_ADMIN") or object === user',
            processor: UserHashPasswordProcessor::class
        )
    ],
    normalizationContext: ["groups" => ["user:read"]],
    denormalizationContext: ["groups" => ["user:write"]]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: "doctrine.ulid_generator")]
    #[Groups(["user:read", "up:restaurant:read"])]
    private ?Ulid $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Length(max: 180, maxMessage: "L'adresse e-mail ne peut pas dépasser {{ limit }} caractères")]
    #[Assert\Email(message: "L'adresse e-mail renseignée n'est pas valide")]
    #[Assert\NotBlank(message: "L'adresse e-mail est obligatoire")]
    #[Groups(["user:read", "user:write:post", "up:restaurant:read"])]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilter::STRATEGY_START)]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilter::STRATEGY_PARTIAL)]
    // TODO: specify on settings page that email can only be changed through Contact Us form (maybe create later a specific process for user to do it by himself, with email confirmation)
    private ?string $email = null;

    #[ORM\Column(type: Types::JSON, options: ["jsonb" => true])]
    #[Groups(["user:read", "user:write"])]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilter::STRATEGY_EXACT)] // TODO: check if working as expected (find one role at a time)
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_ONLY)] // TODO: prevent "classic" admin from adding ROLE_ADMIN to another user
    #[AppAssert\AreRolesAllowed]
    private array $roles = [];

    // TODO: add a reset password process (can be triggered anonymously if email is provided, or by admin)
    #[Assert\PasswordStrength(["message" => "Ce mot de passe est trop vulnérable. Veuillez choisir un mot de passe plus sécurisé."])]
    #[Assert\NotCompromisedPassword(message: "Ce mot de passe est présent dans une fuite de données connue. Veuillez en choisir un autre.", skipOnError: true)]
    #[Assert\Length(min: 12, max: 128, minMessage: "Ce mot de passe est trop court, il doit compter au moins {{ limit }} caractères", maxMessage: "Ce mot de passe est trop long, il ne doit pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank(message: "Un mot de passe est obligatoire", groups: ["postValidation"])]
    #[Groups(["user:write"])]
    #[ApiProperty(security: 'object === user or object === null')]
    #[SerializedName("password")]
    protected ?string $plainPassword = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Restaurant::class, orphanRemoval: true, cascade: ["persist"])]
    #[ApiFilter(ExistsFilter::class)]
    #[Groups(["user:read"])]
    private Collection $restaurants;

    #[ORM\Column(options: ["default" => true])]
    #[Groups(["user:read", "user:write", "up:restaurant:read"])]
    #[ApiFilter(BooleanFilter::class)]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_ONLY)]
    private ?bool $enabled = null;

    #[ORM\Column(options: ["default" => false])]
    #[Groups(["user:read", "user:write", "up:restaurant:read"])]
    #[ApiFilter(BooleanFilter::class)]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_ONLY)]
    private ?bool $verified = null;

    public function __construct()
    {
        $this->restaurants = new ArrayCollection();
        $this->enabled = true;
        $this->verified = false;
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function isPlainPasswordFilled(): bool
    {
        return !is_null($this->plainPassword);
    }

    // TODO: need a confirmation this is the right way of doing things with API before using that
    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    // Password is hashed directly into this entity method to keep plainPassword private
    public function hashPassword(UserPasswordHasherInterface $passwordHasher): static
    {
        $hashedPassword = $passwordHasher->hashPassword($this, $this->plainPassword);
        return $this->setPassword($hashedPassword);
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Restaurant>
     */
    public function getRestaurants(): Collection
    {
        return $this->restaurants;
    }

    public function addRestaurant(Restaurant $restaurant): static
    {
        if (!$this->restaurants->contains($restaurant)) {
            $this->restaurants->add($restaurant);
            $restaurant->setOwner($this);
        }

        return $this;
    }

    public function removeRestaurant(Restaurant $restaurant): static
    {
        if ($this->restaurants->removeElement($restaurant)) {
            // set the owning side to null (unless already changed)
            if ($restaurant->getOwner() === $this) {
                $restaurant->setOwner(null);
            }
        }

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): static
    {
        $this->verified = $verified;

        return $this;
    }

    public function areRestaurantsPublic(): bool
    {
        return $this->isEnabled() && $this->isVerified();
    }
}
