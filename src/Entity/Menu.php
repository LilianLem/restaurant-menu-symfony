<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\MenuRepository;
use App\Security\ApiSecurityExpressionDirectory;
use App\State\MenuStateProcessor;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_ADMIN") or object.getOwner() === user' // TODO: allow users to get only menus on owned restaurants
        ),
        new Get(
            normalizationContext: ["groups" => ["menu:read", "menu:read:self", "menu:read:get", "section:read", "up:menu:read", "up:restaurant:read"]],
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_PUBLIC_OBJECT
        ),
        new Post(
            denormalizationContext: ["groups" => ["menu:write", "menu:write:post"]],
            security: ApiSecurityExpressionDirectory::LOGGED_USER,
            processor: MenuStateProcessor::class
        ),
        new Delete(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER // TODO: extra security to prevent deleting by mistake (user confirmation)
        ),
        new Patch(
            denormalizationContext: ["groups" => ["menu:write", "menu:write:update"]],
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER
        )
    ],
    normalizationContext: ["groups" => ["menu:read", "menu:read:self"]],
    denormalizationContext: ["groups" => ["menu:write"]],
)]
#[ApiFilter(SearchFilter::class, properties: [
    "menuSections.section" => SearchFilter::STRATEGY_EXACT,
    "menuSections.section.sectionProducts.product" => SearchFilter::STRATEGY_EXACT,
    "menuRestaurants.restaurant" => SearchFilter::STRATEGY_EXACT,
    "menuRestaurants.restaurant.owner" => SearchFilter::STRATEGY_EXACT
])]
#[ApiFilter(BooleanFilter::class, properties: ["menuRestaurants.visible"])]
class Menu implements OwnedEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: "doctrine.ulid_generator")]
    #[Groups(["menu:read", "up:section:read"])]
    private ?Ulid $id = null;

    #[ORM\Column(length: 128)]
    #[Assert\Length(max: 128, maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank(message: "Le nom du menu est obligatoire")]
    #[Groups(["menu:read", "menu:write", "up:section:read"])]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilter::STRATEGY_PARTIAL)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: "La description ne doit pas dépasser {{ limit }} caractères")]
    #[Groups(["menu:read", "menu:write", "up:section:read"])]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: MenuSection::class, orphanRemoval: true, cascade: ["persist", "remove"])]
    #[Groups(["menu:read"])]
    #[ApiFilter(ExistsFilter::class)]
    private Collection $menuSections;

    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: RestaurantMenu::class, orphanRemoval: true, cascade: ["persist"])]
    #[Groups(["menu:read:self", "up:menu:read"])]
    private Collection $menuRestaurants;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "Le nom de l'icône ne doit pas dépasser {{ limit }} caractères")]
    #[Groups(["menu:read", "menu:write", "up:section:read"])]
    #[ApiFilter(ExistsFilter::class)]
    private ?string $icon = null;

    #[ORM\Column(nullable: true, options: ["unsigned" => true])]
    #[Assert\PositiveOrZero(message: "Le prix ne peut pas être négatif")]
    #[Assert\LessThanOrEqual(100000000, message: "Le prix ne peut pas être aussi élevé")]
    #[Groups(["menu:read", "menu:write", "up:section:read"])]
    #[ApiFilter(RangeFilter::class)]
    #[ApiFilter(ExistsFilter::class)]
    private ?int $price = null;

    #[ORM\Column(options: ["default" => false])]
    #[Groups(["menu:read", "menu:write:update", "up:section:read"])]
    #[ApiFilter(BooleanFilter::class)]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    private ?bool $inTrash = null;

    #[Groups(["menu:write:post"])]
    #[Assert\NotBlank(message: "Un restaurant doit être renseigné pour créer un menu")]
    #[AppAssert\IsSelfOwned(options: ["message" => "Ce restaurant ne vous appartient pas"])]
    #[SerializedName("firstRestaurant")]
    /** Only used for API POST operations in related StateProcessor */
    private ?Restaurant $restaurantForInit = null;

    public function __construct()
    {
        $this->menuSections = new ArrayCollection();
        $this->menuRestaurants = new ArrayCollection();
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
     * @return Collection<int, MenuSection>
     */
    public function getMenuSections(): Collection
    {
        return $this->menuSections;
    }

    public function addMenuSection(MenuSection $menuSection): static
    {
        if (!$this->menuSections->contains($menuSection)) {
            $this->menuSections->add($menuSection);
            $menuSection->setMenu($this);
        }

        return $this;
    }

    public function removeMenuSection(MenuSection $menuSection): static
    {
        if ($this->menuSections->removeElement($menuSection)) {
            // set the owning side to null (unless already changed)
            if ($menuSection->getMenu() === $this) {
                $menuSection->setMenu(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RestaurantMenu>
     */
    public function getMenuRestaurants(): Collection
    {
        return $this->menuRestaurants;
    }

    public function addMenuRestaurant(RestaurantMenu $menuRestaurant): static
    {
        if (!$this->menuRestaurants->contains($menuRestaurant)) {
            $this->menuRestaurants->add($menuRestaurant);
            $menuRestaurant->setMenu($this);
        }

        return $this;
    }

    public function removeMenuRestaurant(RestaurantMenu $menuRestaurant): static
    {
        if ($this->menuRestaurants->removeElement($menuRestaurant)) {
            // set the owning side to null (unless already changed)
            if ($menuRestaurant->getMenu() === $this) {
                $menuRestaurant->setMenu(null);
            }
        }

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): static
    {
        $this->price = $price;

        return $this;
    }

    #[Groups(["menu:read:get"])]
    public function getMaxSectionRank(): int
    {
        if($this->getMenuSections()->isEmpty()) {
            return 0;
        }

        return $this->getMenuSections()->reduce(fn(int $maxRank, MenuSection $menuSection): int => $menuSection->getRank() > $maxRank ? $menuSection->getRank() : $maxRank, 0);
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
        if($this->isInTrash()) {
            return false;
        }

        return $this->getMenuRestaurants()->exists(
            fn(int $key, RestaurantMenu $restaurantMenu) => $restaurantMenu->isVisible() && $restaurantMenu->getRestaurant()->isPublic()
        );
    }

    public function getOwner(): ?User
    {
        return $this->getMenuRestaurants()->first()->getRestaurant()->getOwner();
    }

    public function getRestaurantForInit(): ?Restaurant
    {
        return $this->restaurantForInit;
    }

    public function setRestaurantForInit(Restaurant $restaurantForInit): static
    {
        $this->restaurantForInit = $restaurantForInit;

        return $this;
    }
}
