<?php

namespace App\Entity;

use App\Repository\RestaurantMenuRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
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
class RestaurantMenu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ["unsigned" => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'restaurantMenus')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Restaurant $restaurant = null;

    #[ORM\ManyToOne(inversedBy: 'menuRestaurants')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Menu $menu = null;

    #[ORM\Column]
    private ?bool $visible = null;

    #[ORM\Column(options: ["unsigned" => true])]
    #[Assert\Positive(message: "Le rang doit être positif")]
    #[Assert\NotBlank]
    private ?int $rank = null;

    public function getId(): ?int
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
