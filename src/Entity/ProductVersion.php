<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
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
#[ApiResource(
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_ADMIN") or object.getOwner() === user' // TODO: allow users to get only versions of products on sections on menus on owned restaurants
        ),
        new Get(
            security: 'object.getOwner() === user or object.isPublic()'
        ),
        new Post(
            denormalizationContext: ["groups" => ["productVersion:write", "productVersion:write:post"]],
            security: 'is_granted("ROLE_USER")' // TODO: force creating a version of a product on a section on a menu of a self-owned restaurant
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN") or object.getOwner() === user'
        ),
        new Patch(
            security: 'is_granted("ROLE_ADMIN") or object.getOwner() === user'
        )
    ],
    normalizationContext: ["groups" => ["productVersion:read", "product:read"]],
    denormalizationContext: ["groups" => ["productVersion:write"]],
)]
class ProductVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ["unsigned" => true])]
    #[Groups(["productVersion:read"])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'versions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["productVersion:read", "productVersion:write:post"])]
    #[Assert\NotBlank(message: "La variante de produit doit être liée à un produit")]
    private readonly ?Product $product;

    #[ORM\Column(length: 128)]
    #[Assert\Length(max: 128, maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank(message: "Le nom de la variante du produit est obligatoire")]
    #[Groups(["productVersion:read", "productVersion:write"])]
    private ?string $name = null;

    #[ORM\Column(nullable: true, options: ["unsigned" => true])]
    #[Assert\PositiveOrZero(message: "Le prix ne peut pas être négatif")]
    #[Groups(["productVersion:read", "productVersion:write"])]
    private ?int $price = null;

    #[ORM\Column(options: ["default" => true])]
    #[Groups(["productVersion:read", "productVersion:write"])]
    private ?bool $visible = null;

    public function __construct(Product $product)
    {
        $this->visible = true;
        $this->product = $product;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /*public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }*/

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

    public function isPublic(): bool
    {
        return $this->getProduct()->isPublic() && $this->isVisible();
    }

    public function getOwner(): ?User
    {
        return $this->getProduct()->getOwner();
    }
}
