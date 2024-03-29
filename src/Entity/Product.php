<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\ProductRepository;
use App\Security\ApiSecurityExpressionDirectory;
use App\State\ProductStateProcessor;
use App\Validator as AppAssert;
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
            security: 'is_granted("ROLE_ADMIN") or object.getOwner() === user' // TODO: allow users to get only products on sections on menus on owned restaurants
        ),
        new Get(
            normalizationContext: ["groups" => ["product:read", "product:read:self", "up:product:read", "up:section:read", "up:menu:read", "up:restaurant:read"]],
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER_OR_PUBLIC_OBJECT
        ),
        new Post(
            denormalizationContext: ["groups" => ["product:write", "product:write:post"]],
            security: ApiSecurityExpressionDirectory::LOGGED_USER,
            processor: ProductStateProcessor::class
        ),
        new Delete(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER // TODO: extra security to prevent deleting by mistake (user confirmation)
        ),
        new Patch(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER
        )
    ],
    normalizationContext: ["groups" => ["product:read", "product:read:self"]],
    denormalizationContext: ["groups" => ["product:write"]]
)]
#[ApiFilter(SearchFilter::class, properties: [
    "sectionProducts.section" => SearchFilter::STRATEGY_EXACT,
    "sectionProducts.section.sectionMenu.menu" => SearchFilter::STRATEGY_EXACT,
    "sectionProducts.section.sectionMenu.menu.menuRestaurants.restaurant" => SearchFilter::STRATEGY_EXACT,
    "sectionProducts.section.sectionMenu.menu.menuRestaurants.restaurant.owner" => SearchFilter::STRATEGY_EXACT
])]
#[ApiFilter(BooleanFilter::class, properties: ["sectionProducts.visible"])]
class Product implements OwnedEntityInterface
{
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
    #[Groups(["product:read:self", "section:read:self"])]
    #[ApiFilter(ExistsFilter::class)]
    private Collection $versions;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: SectionProduct::class, orphanRemoval: true, cascade: ["persist", "detach"])]
    #[Groups(["product:read:self", "up:product:read"])]
    private Collection $productSections;

    #[Groups(["product:write:post"])]
    #[Assert\NotBlank(message: "Une section doit être renseignée pour créer un produit")]
    #[AppAssert\IsSelfOwned(options: ["message" => "Cette section ne vous appartient pas"])]
    #[SerializedName("firstSection")]
    /** Only used for API POST operations in related StateProcessor */
    private ?Section $sectionForInit = null;

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
    public function getProductSections(): Collection
    {
        return $this->productSections;
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

    public function getOwner(): ?User
    {
        return $this->getProductSections()->first()->getSection()->getOwner();
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
}
