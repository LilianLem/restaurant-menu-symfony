<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Entity\Interface\IndirectSoftDeleteableEntityInterface;
use App\Entity\Interface\RankedEntityInterface;
use App\Entity\Trait\OwnedEntityTrait;
use App\Entity\Trait\SoftDeleteableEntityTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Repository\ProductRepository;
use App\Security\ApiSecurityExpressionDirectory;
use App\State\ProductStateProcessor;
use App\State\RankedEntityStateProcessor;
use App\Validator as AppAssert;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: ApiSecurityExpressionDirectory::LOGGED_USER
        ),
        new Get(
            normalizationContext: ["groups" => ["product:read", "product:read:self", "product:read:get", "up:product:read", "up:section:read", "up:menu:read", "up:restaurant:read"]],
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_PUBLIC_OBJECT
        ),
        new Post(
            denormalizationContext: ["groups" => ["product:write", "product:write:post"]],
            security: ApiSecurityExpressionDirectory::LOGGED_USER,
            validationContext: ["groups" => ["Default", "postValidation"]],
            processor: ProductStateProcessor::class
        ),
        new Delete(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER, // TODO: extra security to prevent deleting by mistake (user confirmation)
            processor: RankedEntityStateProcessor::class
        ),
        new Patch(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER
        )
    ],
    normalizationContext: ["groups" => ["product:read", "product:read:self"]],
    denormalizationContext: ["groups" => ["product:write"]]
)]
#[ApiFilter(SearchFilter::class, properties: [
    "productSections.section" => SearchFilter::STRATEGY_EXACT,
    "productSections.section.sectionMenu.menu" => SearchFilter::STRATEGY_EXACT,
    "productSections.section.sectionMenu.menu.menuRestaurants.restaurant" => SearchFilter::STRATEGY_EXACT,
    "productSections.section.sectionMenu.menu.menuRestaurants.restaurant.owner" => SearchFilter::STRATEGY_EXACT
])]
#[ApiFilter(BooleanFilter::class, properties: ["sectionProducts.visible"])]
class Product implements RankedEntityInterface, IndirectSoftDeleteableEntityInterface
{
    use OwnedEntityTrait, SoftDeleteableEntityTrait, TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: "doctrine.ulid_generator")]
    #[Groups(["product:read"])]
    private ?Ulid $id = null;

    #[ORM\Column(length: 128)]
    #[Assert\Length(max: 128, maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank(message: "Le nom du produit est obligatoire")]
    #[Groups(["product:read", "product:write"])]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilter::STRATEGY_PARTIAL)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 500, maxMessage: "La description ne doit pas dépasser {{ limit }} caractères")]
    #[Groups(["product:read", "product:write"])]
    private ?string $description = null;

    #[ORM\Column(nullable: true, options: ["unsigned" => true, "default" => 0])]
    #[Assert\PositiveOrZero(message: "Le prix ne peut pas être négatif")]
    #[Assert\LessThanOrEqual(100000000, message: "Le prix ne peut pas être aussi élevé")]
    #[Groups(["product:read", "product:write"])]
    #[ApiFilter(RangeFilter::class)]
    #[ApiFilter(ExistsFilter::class)]
    private ?int $price = null;

    // TODO: allow excluding products by allergen
    #[ORM\ManyToMany(targetEntity: Allergen::class, inversedBy: 'products')]
    #[Groups(["product:read", "product:write"])]
    #[ApiFilter(ExistsFilter::class)]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilter::STRATEGY_EXACT)]
    private Collection $allergens;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductVersion::class, orphanRemoval: true, cascade: ["persist"])]
    #[ORM\OrderBy(["rank" => "ASC"])]
    #[Groups(["product:read:self", "section:read:self"])]
    #[ApiFilter(ExistsFilter::class)]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    private Collection $versions;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: SectionProduct::class, orphanRemoval: true, cascade: ["persist", "detach"])]
    #[Groups(["product:read:self", "up:product:read"])]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    private Collection $productSections;

    #[Groups(["product:write:post"])]
    #[Assert\NotBlank(message: "Une section doit être renseignée pour créer un produit", groups: ["postValidation"])]
    #[AppAssert\IsSelfOwned(options: ["message" => "Cette section ne vous appartient pas"])]
    #[SerializedName("firstSection")]
    /** Only used for API POST operations in related StateProcessor */
    private ?Section $sectionForInit = null;

    #[Groups(["product:write:post"])]
    #[Assert\Positive(message: "Le rang doit être positif")]
    #[Assert\LessThan(10000, message: "Le rang doit être inférieur à 10000")]
    #[SerializedName("firstSectionRank")]
    /** Only used for API POST operations in related StateProcessor */
    private ?int $rankOnSectionForInit = null;

    public function __construct()
    {
        $this->allergens = new ArrayCollection();
        $this->versions = new ArrayCollection();
        $this->productSections = new ArrayCollection();
    }

    public function getId(): ?Ulid
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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, Allergen>
     */
    public function getAllergens(): Collection
    {
        return $this->allergens;
    }

    public function addAllergen(Allergen $allergen): static
    {
        if (!$this->allergens->contains($allergen)) {
            $this->allergens->add($allergen);
        }

        return $this;
    }

    public function removeAllergen(Allergen $allergen): static
    {
        $this->allergens->removeElement($allergen);

        return $this;
    }

    /**
     * @return Collection<int, ProductVersion>
     */
    public function getVersions(): Collection
    {
        return $this->versions;
    }

    /**
     * @return Collection<int, ProductVersion>
     */
    #[Groups(["product:read:self", "section:read:self"])]
    #[ApiFilter(ExistsFilter::class)]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::NOT_ADMIN_NOR_OWNER_AND_NOT_NULL_OBJECT)]
    #[SerializedName("versions")]
    public function getPublicVersions(): Collection
    {
        return new ArrayCollection(
            $this->versions->filter(
                fn(ProductVersion $version) => $version->isVisible()
            )->getValues()
        );
    }

    public function addVersion(ProductVersion $version): static
    {
        if (!$this->versions->contains($version)) {
            $this->versions->add($version);
            $version->setProduct($this);
        }

        return $this;
    }

    public function removeVersion(ProductVersion $version): static
    {
        if ($this->versions->removeElement($version)) {
            // set the owning side to null (unless already changed)
            if ($version->getProduct() === $this) {
                $version->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SectionProduct>
     */
    public function getRankingEntities(): Collection
    {
        return $this->getProductSections();
    }

    /**
     * @return Collection<int, SectionProduct>
     */
    public function getProductSections(): Collection
    {
        return $this->productSections;
    }

    /**
     * @return Collection<int, SectionProduct>
     */
    #[Groups(["product:read:self", "up:product:read"])]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::NOT_ADMIN_NOR_OWNER_AND_NOT_NULL_OBJECT)]
    #[SerializedName("productSections")]
    public function getPublicProductSections(): Collection
    {
        return new ArrayCollection(
            $this->productSections->filter(
                fn(SectionProduct $sProduct) => $sProduct->isVisible() && $sProduct->getSection()->isPublic()
            )->getValues()
        );
    }

    public function addProductSection(SectionProduct $productSection): static
    {
        if (!$this->productSections->contains($productSection)) {
            $this->productSections->add($productSection);
            $productSection->setProduct($this);
        }

        return $this;
    }

    public function removeProductSection(SectionProduct $productSection): static
    {
        if ($this->productSections->removeElement($productSection)) {
            // set the owning side to null (unless already changed)
            if ($productSection->getProduct() === $this) {
                $productSection->setProduct(null);
            }
        }

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->getProductSections()->exists(
            fn(int $key, SectionProduct $sectionProduct) => $sectionProduct->isVisible() && $sectionProduct->getSection()->isPublic()
        );
    }

    public function getSectionForInit(): ?Section
    {
        return $this->sectionForInit;
    }

    /** To use only when creating a new product */
    public function setSectionForInit(Section $sectionForInit): static
    {
        $this->sectionForInit = $sectionForInit;

        return $this;
    }

    public function getRankOnSectionForInit(): ?int
    {
        return $this->rankOnSectionForInit;
    }

    /** To use only when creating a new product */
    public function setRankOnSectionForInit(int $rankOnSectionForInit): static
    {
        $this->rankOnSectionForInit = $rankOnSectionForInit;

        return $this;
    }

    #[Groups(["product:read:get"])]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    public function getMaxVersionRank(): int
    {
        if($this->getVersions()->isEmpty()) {
            return 0;
        }

        return $this->getVersions()->reduce(fn(int $maxRank, ProductVersion $version): int => $version->getRank() > $maxRank ? $version->getRank() : $maxRank, 0);
    }

    #[Groups(["product:read"])]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    public function getCreatedAt(): ?Carbon
    {
        return $this->createdAt;
    }

    #[Groups(["product:read"])]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_NULL_OBJECT)]
    public function getUpdatedAt(): ?Carbon
    {
        return $this->updatedAt;
    }

    public function getChildren(): Collection
    {
        return $this->getVersions();
    }

    public function getParents(): Collection
    {
        return $this->getProductSections();
    }
}
