<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ["unsigned" => true])]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?int $id = null;

    #[ORM\Column(length: 128)]
    #[Assert\Length(max: 128, maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: "La description ne doit pas dépasser {{ limit }} caractères")]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?string $description = null;

    #[ORM\Column(options: ["default" => false])]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?bool $visible = null;

    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: MenuSection::class, orphanRemoval: true, cascade: ["persist", "remove"])]
    #[Groups(["getRestaurants", "getMenus"])]
    private Collection $menuSections;

    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: RestaurantMenu::class, orphanRemoval: true, cascade: ["persist"])]
    #[Groups(["getMenus", "getSections", "getProducts"])]
    private Collection $menuRestaurants;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "Le nom de l'icône ne doit pas dépasser {{ limit }} caractères")]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?string $icon = null;

    #[ORM\Column(nullable: true, options: ["unsigned" => true])]
    #[Assert\PositiveOrZero(message: "Le prix ne peut pas être négatif")]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?int $price = null;

    #[ORM\Column(options: ["default" => false])]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?bool $inTrash = null;

    public function __construct()
    {
        $this->visible = false;
        $this->menuSections = new ArrayCollection();
        $this->menuRestaurants = new ArrayCollection();
        $this->inTrash = false;
    }

    public function getId(): ?int
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

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;

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
}
