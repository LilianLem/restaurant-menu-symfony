<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\ProductVersionRepository;
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

#[ORM\Entity(repositoryClass: ProductVersionRepository::class)]
#[ORM\UniqueConstraint("product_version_unique", columns: ["product_id", "name"])]
#[UniqueEntity(
    fields: ["product", "name"],
    errorPath: "name",
    message: "Une version avec ce nom existe déjà",
)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: ApiSecurityExpressionDirectory::ADMIN_ONLY
        ),
        new Get(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_PUBLIC_OBJECT
        ),
        new Post(
            denormalizationContext: ["groups" => ["productVersion:write", "productVersion:write:post"]],
            security: ApiSecurityExpressionDirectory::LOGGED_USER,
            processor: RankingEntityStateProcessor::class
        ),
        new Delete(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER,
            processor: RankingEntityStateProcessor::class
        ),
        new Patch(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER,
            processor: RankingEntityStateProcessor::class
        )
    ],
    normalizationContext: ["groups" => ["productVersion:read", "product:read"]],
    denormalizationContext: ["groups" => ["productVersion:write"]],
)]
class ProductVersion implements RankingEntityInterface, RankedEntityInterface, IndirectSoftDeleteableEntityInterface
{
    use OwnedEntityTrait, SoftDeleteableEntityTrait, TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: "doctrine.ulid_generator")]
    #[Groups(["productVersion:read", "product:read"])]
    private ?Ulid $id = null;

    #[ORM\ManyToOne(inversedBy: 'versions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["productVersion:read", "productVersion:write:post"])]
    #[Assert\NotBlank(message: "La variante de produit doit être liée à un produit")]
    #[AppAssert\IsSelfOwned(options: ["message" => "Ce produit ne vous appartient pas"])]
    private ?Product $product;

    #[ORM\Column(length: 128)]
    #[Assert\Length(max: 128, maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank(message: "Le nom de la variante du produit est obligatoire")]
    #[Groups(["productVersion:read", "productVersion:write", "product:read"])]
    private ?string $name = null;

    #[ORM\Column(nullable: true, options: ["unsigned" => true])]
    #[Assert\PositiveOrZero(message: "Le prix ne peut pas être négatif")]
    #[Assert\LessThanOrEqual(100000000, message: "Le prix ne peut pas être aussi élevé")]
    #[Groups(["productVersion:read", "productVersion:write", "product:read"])]
    private ?int $price = null;

    #[ORM\Column(options: ["default" => true])]
    #[Groups(["productVersion:read", "productVersion:write", "product:read"])]
    private ?bool $visible = null;

    #[ORM\Column(options: ["unsigned" => true])]
    #[Assert\Positive(message: "Le rang doit être positif")]
    #[Assert\LessThan(10000, message: "Le rang doit être inférieur à 10000")]
    #[Groups(["productVersion:read", "productVersion:write", "product:read"])]
    private ?int $rank = null;

    public function __construct(Product $product)
    {
        $this->visible = true;
        $this->product = $product;
    }

    public function getId(): ?Ulid
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

    public function isPublic(): bool
    {
        return $this->getProduct()->isPublic() && $this->isVisible();
    }

    public function getRankingEntities(): static
    {
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

    public function getSiblings(): Collection
    {
        $siblings = $this->getProduct()->getVersions();
        return $siblings->filter(fn(self $element) => $element->getId() !== $this->getId());
    }

    public function getRankedEntity(): static
    {
        return $this;
    }

    public function getMaxParentCollectionRank(): ?int
    {
        return $this->getProduct()?->getMaxVersionRank() ?? null;
    }

    #[Groups(["productVersion:read", "product:read"])]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    public function getCreatedAt(): ?Carbon
    {
        return $this->createdAt;
    }

    #[Groups(["productVersion:read", "product:read"])]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    public function getUpdatedAt(): ?Carbon
    {
        return $this->updatedAt;
    }

    public function getChildren(): null
    {
        return null;
    }

    public function getParents(): ?Product
    {
        return $this->getProduct();
    }
}
