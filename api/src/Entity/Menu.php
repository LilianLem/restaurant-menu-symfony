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
use App\Entity\Interface\DirectSoftDeleteableEntityInterface;
use App\Entity\Interface\RankedEntityInterface;
use App\Entity\Trait\OwnedEntityTrait;
use App\Entity\Trait\SoftDeleteableEntityTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Repository\MenuRepository;
use App\Security\ApiSecurityExpressionDirectory;
use App\State\DirectSoftDeleteableEntityStateProcessor;
use App\State\MenuStateProcessor;
use App\Validator as AppAssert;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: ApiSecurityExpressionDirectory::LOGGED_USER
        ),
        new Get(
            normalizationContext: ["groups" => ["menu:read", "menu:read:self", "menu:read:get", "section:read", "up:menu:read", "up:restaurant:read"]],
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_PUBLIC_OBJECT
        ),
        new Post(
            denormalizationContext: ["groups" => ["menu:write", "menu:write:post"]],
            security: ApiSecurityExpressionDirectory::LOGGED_USER,
            validationContext: ["groups" => ["Default", "postValidation"]],
            processor: MenuStateProcessor::class
        ),
        new Delete(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER, // TODO: extra security to prevent deleting by mistake (user confirmation)
            processor: DirectSoftDeleteableEntityStateProcessor::class
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
#[Gedmo\SoftDeleteable(hardDelete: false)]
class Menu implements RankedEntityInterface, DirectSoftDeleteableEntityInterface
{
    use OwnedEntityTrait, SoftDeleteableEntityTrait, TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: "doctrine.ulid_generator")]
    #[Groups(["menu:read", "up:section:read"])]
    private ?Ulid $id = null;

    #[ORM\Column(length: 64)]
    #[Assert\Length(max: 64, maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank(message: "Le nom du menu est obligatoire")]
    #[Groups(["menu:read", "menu:write", "up:section:read"])]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilter::STRATEGY_PARTIAL)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: "La description ne doit pas dépasser {{ limit }} caractères")]
    #[Groups(["menu:read", "menu:write", "up:section:read"])]
    private ?string $description = null;

    #[ORM\Column(length: 96, unique: true)]
    #[Assert\Length(max: 96, maxMessage: "L'identifiant ne doit pas dépasser {{ limit }} caractères")]
    #[Assert\Unique(message: "Cet identifiant de menu est déjà utilisé")]
    #[Groups(["menu:read", "up:section:read"])]
    #[Gedmo\Slug(fields: ["name"])]
    private ?string $slug = null;

    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: MenuSection::class, orphanRemoval: true, cascade: ["persist", "remove"])]
    #[ORM\OrderBy(["rank" => "ASC"])]
    #[Groups(["menu:read"])]
    #[ApiFilter(ExistsFilter::class)]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    private Collection $menuSections;

    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: RestaurantMenu::class, orphanRemoval: true, cascade: ["persist"])]
    #[Groups(["menu:read:self", "up:menu:read"])]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    private Collection $menuRestaurants;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "Le nom de l'icône ne doit pas dépasser {{ limit }} caractères")]
    ##[Groups(["menu:read", "menu:write", "up:section:read"])] TODO: handle icons
    #[ApiFilter(ExistsFilter::class)]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::NEVER)] // Remove security when field will be ready
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
    #[Assert\NotBlank(message: "Un restaurant doit être renseigné pour créer un menu", groups: ["postValidation"])]
    #[AppAssert\IsSelfOwned(options: ["message" => "Ce restaurant ne vous appartient pas"])]
    #[SerializedName("firstRestaurant")]
    /** Only used for API POST operations in related StateProcessor */
    private ?Restaurant $restaurantForInit = null;

    #[Groups(["menu:write:post"])]
    #[Assert\Positive(message: "Le rang doit être positif")]
    #[Assert\LessThan(10000, message: "Le rang doit être inférieur à 10000")]
    #[SerializedName("firstRestaurantRank")]
    /** Only used for API POST operations in related StateProcessor */
    private ?int $rankOnRestaurantForInit = null;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, MenuSection>
     */
    public function getMenuSections(): Collection
    {
        return $this->menuSections;
    }

    /**
     * @return Collection<int, MenuSection>
     */
    #[Groups(["menu:read"])]
    #[ApiFilter(ExistsFilter::class)]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::NOT_ADMIN_NOR_OWNER_AND_NOT_NULL_OBJECT)]
    #[SerializedName("menuSections")]
    public function getPublicMenuSections(): Collection
    {
        return new ArrayCollection(
            $this->menuSections->filter(
                fn(MenuSection $mSection) => $mSection->isVisible()
            )->getValues()
        );
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
    public function getRankingEntities(): Collection
    {
        return $this->getMenuRestaurants();
    }

    /**
     * @return Collection<int, RestaurantMenu>
     */
    public function getMenuRestaurants(): Collection
    {
        return $this->menuRestaurants;
    }

    /**
     * @return Collection<int, RestaurantMenu>
     */
    #[Groups(["menu:read:self", "up:menu:read"])]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::NOT_ADMIN_NOR_OWNER_AND_NOT_NULL_OBJECT)]
    #[SerializedName("menuRestaurants")]
    public function getPublicMenuRestaurants(): Collection
    {
        return new ArrayCollection(
            $this->menuRestaurants->filter(
                fn(RestaurantMenu $rMenu) => $rMenu->isVisible() && $rMenu->getRestaurant()->isPublic()
            )->getValues()
        );
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

    public function getRestaurantForInit(): ?Restaurant
    {
        return $this->restaurantForInit;
    }

    public function setRestaurantForInit(Restaurant $restaurantForInit): static
    {
        $this->restaurantForInit = $restaurantForInit;

        return $this;
    }

    public function getRankOnRestaurantForInit(): ?int
    {
        return $this->rankOnRestaurantForInit;
    }

    public function setRankOnRestaurantForInit(int $rankOnRestaurantForInit): static
    {
        $this->rankOnRestaurantForInit = $rankOnRestaurantForInit;

        return $this;
    }

    #[Groups(["menu:read", "up:section:read"])]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    public function getCreatedAt(): ?Carbon
    {
        return $this->createdAt;
    }

    #[Groups(["menu:read", "up:section:read"])]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    public function getUpdatedAt(): ?Carbon
    {
        return $this->updatedAt;
    }

    public function getChildren(): Collection
    {
        return $this->getMenuSections();
    }

    public function getParents(): Collection
    {
        return $this->getMenuRestaurants();
    }
}
