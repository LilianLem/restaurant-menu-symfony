<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AllergenRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AllergenRepository::class)]
#[UniqueEntity("name", message: "Cet allergène existe déjà")]
#[ApiResource]
class Allergen
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ["unsigned" => true])]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    #[Assert\Length(max: 64, maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank(message: "Le nom de l'allergène est obligatoire")]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'allergens')]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
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

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->addAllergen($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            $product->removeAllergen($this);
        }

        return $this;
    }
}
