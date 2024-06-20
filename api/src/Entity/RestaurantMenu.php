<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Interface\JoinEntityInterface;
use App\Entity\Interface\RankingEntityInterface;
use App\Entity\Trait\OwnedEntityTrait;
use App\Entity\Trait\SoftDeleteableEntityTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Repository\RestaurantMenuRepository;
use App\Security\ApiSecurityExpressionDirectory;
use App\State\RankingEntityStateProcessor;
use App\Validator as AppAssert;
use Carbon\Carbon;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RestaurantMenuRepository::class)]
#[ORM\UniqueConstraint("restaurant_menu_unique", columns: ["restaurant_id", "menu_id"])]
#[UniqueEntity(
    fields: ["restaurant", "menu"],
    errorPath: "menu",
    message: "Ce menu est déjà relié au restaurant",
)]
#[ApiResource(
    operations: [
        new Post(
            denormalizationContext: ["groups" => ["restaurantMenu:write", "restaurantMenu:write:post"]],
            security: ApiSecurityExpressionDirectory::LOGGED_USER,
            processor: RankingEntityStateProcessor::class
        ),
        new Delete(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER, // TODO: extra security to prevent deleting by mistake (user confirmation)
            processor: RankingEntityStateProcessor::class
        ),
        new Patch(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER,
            processor: RankingEntityStateProcessor::class
        )
    ],
    normalizationContext: ["groups" => ["restaurantMenu:read", "restaurantMenu:write", "restaurantMenu:write:post"]],
    denormalizationContext: ["groups" => ["restaurantMenu:write"]]
)]
#[AppAssert\CanRankingEntityBeDeleted(options: ["message" => "Ce menu n'est relié qu'à un seul restaurant. Veuillez supprimer le menu directement."], groups: ["self:delete"])]
class RestaurantMenu implements RankingEntityInterface, JoinEntityInterface
{
    use OwnedEntityTrait, SoftDeleteableEntityTrait, TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: "doctrine.ulid_generator")]
    #[Groups(["up:menu:read", "menu:read", "restaurant:read:get", "restaurantMenu:read"])]
    private ?Ulid $id = null;

    #[ORM\ManyToOne(inversedBy: 'restaurantMenus')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Un restaurant doit être renseigné pour pouvoir y lier un menu")]
    #[AppAssert\IsSelfOwned(options: ["message" => "Ce restaurant ne vous appartient pas"])]
    #[Groups(["up:menu:read", "menu:read:self", "restaurantMenu:write:post"])]
    private ?Restaurant $restaurant = null;

    #[ORM\ManyToOne(inversedBy: 'menuRestaurants', cascade: ["persist", "detach"])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Un menu doit être renseigné pour pouvoir y lier un restaurant")]
    #[AppAssert\IsSelfOwned(options: ["message" => "Ce menu ne vous appartient pas"])]
    #[Groups(["restaurant:read:get", "restaurantMenu:write:post"])]
    private ?Menu $menu = null;

    #[ORM\Column(options: ["default" => false])]
    #[Groups(["up:menu:read", "menu:read", "restaurantMenu:write", "restaurant:read:get"])]
    private ?bool $visible = null;

    #[ORM\Column(options: ["unsigned" => true])]
    #[Assert\Positive(message: "Le rang doit être positif")]
    #[Assert\LessThan(10000, message: "Le rang doit être inférieur à 10000")]
    #[Groups(["up:menu:read", "menu:read", "restaurantMenu:write", "restaurant:read:get"])]
    private ?int $rank = null;

    public function __construct()
    {
        $this->visible = false;
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;

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

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }

    /** @return Collection<static> */
    public function getSiblings(): Collection
    {
        $siblings = $this->getRestaurant()->getRestaurantMenus();
        return $siblings->filter(fn(self $element) => $element->getId() !== $this->getId());
    }

    public function getRankedEntity(): ?Menu
    {
        return $this->getMenu();
    }

    public function getMaxParentCollectionRank(): ?int
    {
        return $this->getRestaurant()?->getMaxMenuRank() ?? null;
    }

    #[Groups(["up:menu:read", "menu:read", "restaurant:read:get", "restaurantMenu:read"])]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    public function getCreatedAt(): ?Carbon
    {
        return $this->createdAt;
    }

    #[Groups(["up:menu:read", "menu:read", "restaurant:read:get", "restaurantMenu:read"])]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    public function getUpdatedAt(): ?Carbon
    {
        return $this->updatedAt;
    }

    public function getChildren(): ?Menu
    {
        return $this->getMenu();
    }

    public function getParents(): ?Restaurant
    {
        return $this->getRestaurant();
    }
}
