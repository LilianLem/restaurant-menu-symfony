<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
#[ORM\UniqueConstraint("restaurant_owner_name_unique", columns: ["name", "owner_id"])]
#[UniqueEntity(
    fields: ["name", "owner"],
    errorPath: "name",
    message: "Vous possédez déjà un restaurant avec ce nom",
)]
class Restaurant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ["unsigned" => true])]
    private ?int $id = null;

    #[ORM\Column(length: 128)]
    #[Assert\Length(max: 128, maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "Le lien vers le logo ne doit pas dépasser {{ limit }} caractères")]
    private ?string $logo = null;

    #[ORM\Column]
    private ?bool $visible = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: "La description ne doit pas dépasser {{ limit }} caractères")]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: RestaurantMenu::class, orphanRemoval: true)]
    private Collection $restaurantMenus;

    #[ORM\ManyToOne(inversedBy: 'restaurants')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?User $owner = null;

    public function __construct()
    {
        $this->restaurantMenus = new ArrayCollection();
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
}
