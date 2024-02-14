<?php

namespace App\Entity;

use App\Repository\ProductVersionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductVersionRepository::class)]
#[ORM\UniqueConstraint("product_version_unique", columns: ["product_id", "name"])]
#[UniqueEntity(
    fields: ["product", "name"],
    errorPath: "name",
    message: "Cette version existe déjà",
)]
class ProductVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ["unsigned" => true])]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'versions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Product $product = null;

    #[ORM\Column(length: 128)]
    #[Assert\Length(max: 128, maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?string $name = null;

    #[ORM\Column(nullable: true, options: ["unsigned" => true])]
    #[Assert\PositiveOrZero(message: "Le prix ne peut pas être négatif")]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?int $price = null;

    #[ORM\Column(options: ["default" => true])]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?bool $visible = null;

    public function __construct()
    {
        $this->visible = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): static
    {
        $this->price = $price;

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
}
