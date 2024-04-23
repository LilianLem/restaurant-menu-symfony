<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\RestaurantMenuRepository;
use App\Security\ApiSecurityExpressionDirectory;
use App\State\RankedEntityStateProcessor;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RestaurantMenuRepository::class)]
#[ORM\UniqueConstraint("restaurant_menu_unique", columns: ["restaurant_id", "menu_id"])]
//#[ORM\UniqueConstraint("restaurant_menu_rank_unique", columns: ["restaurant_id", "rank"])]
#[UniqueEntity(
    fields: ["restaurant", "menu"],
    errorPath: "menu",
    message: "Ce menu est déjà relié au restaurant",
)]
/*#[UniqueEntity(
    fields: ["restaurant", "rank"],
    errorPath: "rank",
    message: "Ce rang de menu est déjà assigné sur ce restaurant",
)]*/
#[ApiResource(
    operations: [
        new Post(
            denormalizationContext: ["groups" => ["restaurantMenu:write", "restaurantMenu:write:post"]],
            security: ApiSecurityExpressionDirectory::LOGGED_USER,
            processor: RankedEntityStateProcessor::class
        ),
        new Delete(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER // TODO: extra security to prevent deleting by mistake (user confirmation)
        ),
        new Patch(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER,
            processor: RankedEntityStateProcessor::class
        )
    ],
    normalizationContext: ["groups" => ["restaurantMenu:read", "restaurantMenu:write", "restaurantMenu:write:post"]],
    denormalizationContext: ["groups" => ["restaurantMenu:write"]]
)]
class RestaurantMenu implements OwnedEntityInterface, RankedEntityInterface
{
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
    #[Assert\NotBlank] // TODO: autoset rank if empty
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
        return $this->getRestaurant()->getRestaurantMenus();
    }

    public function getMaxParentCollectionRank(): ?int
    {
        return $this->getRestaurant()?->getMaxMenuRank() ?? null;
    }

    public function getOwner(): ?User
    {
        return $this->getRestaurant()->getOwner();
    }
}
