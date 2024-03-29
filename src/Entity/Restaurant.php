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
use App\Repository\RestaurantRepository;
use App\Security\ApiSecurityExpressionDirectory;
use App\State\RestaurantStateProcessor;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
#[ORM\UniqueConstraint("restaurant_owner_name_unique", columns: ["name", "owner_id"])]
#[UniqueEntity(
    fields: ["name", "owner"],
    errorPath: "name",
    message: "Vous possédez déjà un restaurant avec ce nom",
)]
#[AppAssert\IsRestaurantNameUnique(groups: ["postValidation"])]
#[ApiResource(
    operations: [
        new GetCollection(
            security: ApiSecurityExpressionDirectory::ADMIN_ONLY // TODO: allow users to get only owned restaurants
        ),
        new Get(
            normalizationContext: ["groups" => ["restaurant:read", "restaurant:read:self", "restaurant:read:get", "menu:read", "up:restaurant:read"]],
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_PUBLIC_OBJECT
        ),
        new Post(
            security: ApiSecurityExpressionDirectory::LOGGED_USER,
            validationContext: ["groups" => ["Default", "postValidation"]],
            processor: RestaurantStateProcessor::class
        ),
        new Delete(
            // TODO: extra security to prevent deleting by mistake (strong auth + user confirmation, and eventually force the restaurant to be in trash prior to deletion)
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER
        ),
        new Patch(
            denormalizationContext: ["groups" => ["restaurant:write", "restaurant:write:update"]],
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER
        )
    ],
    normalizationContext: ["groups" => ["restaurant:read", "restaurant:read:self"]],
    denormalizationContext: ["groups" => ["restaurant:write"]]
)]
#[ApiFilter(SearchFilter::class, properties: [
    "restaurantMenus.menu" => SearchFilter::STRATEGY_EXACT,
    "restaurantMenus.menu.menuSections.section" => SearchFilter::STRATEGY_EXACT,
    "restaurantMenus.menu.menuSections.section.sectionProducts.product" => SearchFilter::STRATEGY_EXACT
])]
class Restaurant implements OwnedEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: "doctrine.ulid_generator")]
    #[Groups(["restaurant:read", "up:menu:read"])]
    private ?Ulid $id = null;

    #[ORM\Column(length: 128)]
    #[Assert\Length(max: 128, maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank(message: "Le nom du restaurant est obligatoire")]
    #[Groups(["restaurant:read", "restaurant:write", "up:menu:read"])]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilter::STRATEGY_WORD_START)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "Le lien vers le logo ne doit pas dépasser {{ limit }} caractères")]
    #[Groups(["restaurant:read", "restaurant:write", "up:menu:read"])]
    #[ApiFilter(ExistsFilter::class)]
    private ?string $logo = null;

    #[ORM\Column(options: ["default" => false])]
    #[Groups(["restaurant:read", "restaurant:write", "up:menu:read"])]
    #[ApiFilter(BooleanFilter::class)]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    private ?bool $visible = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: "La description ne doit pas dépasser {{ limit }} caractères")]
    #[Groups(["restaurant:read", "restaurant:write", "up:menu:read"])]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: RestaurantMenu::class, orphanRemoval: true, cascade: ["persist", "remove"])]
    #[Groups(["restaurant:read"])]
    #[ApiFilter(ExistsFilter::class)]
    private Collection $restaurantMenus;

    #[ORM\ManyToOne(inversedBy: 'restaurants')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["restaurant:read:self", "restaurant:write", "up:restaurant:read"])]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilter::STRATEGY_EXACT)]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_ONLY)]
    /** Automatically defined in RestaurantStateProcessor */
    private ?User $owner = null;

    #[ORM\Column(options: ["default" => false])]
    #[Groups(["restaurant:read", "restaurant:write", "up:menu:read"])]
    #[ApiFilter(BooleanFilter::class)]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    private ?bool $inTrash = null;

    public function __construct()
    {
        $this->visible = false;
        $this->restaurantMenus = new ArrayCollection();
        $this->inTrash = false;
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, RestaurantMenu>
     */
    public function getRestaurantMenus(): Collection
    {
        return $this->restaurantMenus;
    }

    public function addRestaurantMenu(RestaurantMenu $restaurantMenu): static
    {
        if (!$this->restaurantMenus->contains($restaurantMenu)) {
            $this->restaurantMenus->add($restaurantMenu);
            $restaurantMenu->setRestaurant($this);
        }

        return $this;
    }

    public function removeRestaurantMenu(RestaurantMenu $restaurantMenu): static
    {
        if ($this->restaurantMenus->removeElement($restaurantMenu)) {
            // set the owning side to null (unless already changed)
            if ($restaurantMenu->getRestaurant() === $this) {
                $restaurantMenu->setRestaurant(null);
            }
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    #[Groups(["restaurant:read:get"])]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    public function getMaxMenuRank(): int
    {
        if($this->getRestaurantMenus()->isEmpty()) {
            return 0;
        }

        return $this->getRestaurantMenus()->reduce(fn(int $maxRank, RestaurantMenu $rm): int => $rm->getRank() > $maxRank ? $rm->getRank() : $maxRank, 0);
    }

    public function isInTrash(): ?bool
    {
        return $this->inTrash;
    }

    public function setInTrash(bool $inTrash): static
    {
        $this->inTrash = $inTrash;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->getOwner()->areRestaurantsPublic() && !$this->isInTrash() && $this->isVisible();
    }
}
