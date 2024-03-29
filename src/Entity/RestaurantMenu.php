<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Patch;
use App\Repository\RestaurantMenuRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RestaurantMenuRepository::class)]
#[ORM\UniqueConstraint("restaurant_menu_unique", columns: ["restaurant_id", "menu_id"])]
#[ORM\UniqueConstraint("restaurant_menu_rank_unique", columns: ["restaurant_id", "rank"])]
#[UniqueEntity(
    fields: ["restaurant", "menu"],
    errorPath: "menu",
    message: "Ce menu est déjà relié au restaurant",
)]
#[UniqueEntity(
    fields: ["restaurant", "rank"],
    errorPath: "rank",
    message: "Ce rang de menu est déjà assigné sur ce restaurant",
)]
#[ApiResource(
    operations: [
        new Patch(
            security: self::ADMIN_OR_OWNER_SECURITY_EXPR
        )
    ],
    denormalizationContext: ["groups" => ["restaurantMenu:write"]]
)]
class RestaurantMenu
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: "doctrine.ulid_generator")]
    #[Groups(["up:menu:read", "menu:read", "restaurant:read:get"])]
    private ?Ulid $id = null;

    #[ORM\ManyToOne(inversedBy: 'restaurantMenus')]
    #[ORM\JoinColumn(nullable: false)]
    //#[Assert\NotBlank]
    #[Groups(["up:menu:read", "menu:read:self"])]
    private ?Restaurant $restaurant = null;

    #[ORM\ManyToOne(inversedBy: 'menuRestaurants', cascade: ["persist", "detach"])]
    #[ORM\JoinColumn(nullable: false)]
    //#[Assert\NotBlank]
    #[Groups(["restaurant:read:get"])]
    private ?Menu $menu = null;

    #[ORM\Column(options: ["default" => false])]
    #[Groups(["up:menu:read", "menu:read", "restaurantMenu:write", "restaurant:read:get"])]
    #[ApiProperty(security: 'is_granted("ROLE_ADMIN") or object?.getRestaurant().getOwner() === user or object === null')]
    private ?bool $visible = null;

    #[ORM\Column(options: ["unsigned" => true])]
    #[Assert\Positive(message: "Le rang doit être positif")]
    #[Assert\NotBlank]
    #[Groups(["up:menu:read", "menu:read", "restaurantMenu:write", "restaurant:read:get"])]
    private ?int $rank = null;

    private const string ADMIN_OR_OWNER_SECURITY_EXPR = 'is_granted("ROLE_ADMIN") or object.getRestaurant().getOwner() === user';
    private const string PROPERTIES_ADMIN_OR_OWNER_SECURITY_EXPR = self::ADMIN_OR_OWNER_SECURITY_EXPR . " or object === null";

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
}
