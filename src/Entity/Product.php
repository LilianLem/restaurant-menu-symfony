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
use ApiPlatform\Metadata\Put;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(
            normalizationContext: ["groups" => ["product:read", "product:read:self", "up:product:read", "up:section:read", "up:menu:read", "up:restaurant:read"]]
        ),
        new Post(),
        new Delete(),
        new Patch(),
        new Put()
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
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ["unsigned" => true])]
    #[Groups(["product:read"])]
    private ?int $id = null;

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
    #[Groups(["product:read", "product:write"])]
    #[ApiFilter(RangeFilter::class)]
    #[ApiFilter(ExistsFilter::class)]
    private ?int $price = null;

    #[ORM\Column(options: ["default" => true])]
    #[Groups(["product:read", "product:write"])]
    #[ApiFilter(BooleanFilter::class)]
    private ?bool $visible = null;

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
    private Collection $sectionProducts;

    public function __construct()
    {
        $this->visible = true;
        $this->allergens = new ArrayCollection();
        $this->versions = new ArrayCollection();
        $this->sectionProducts = new ArrayCollection();
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
    public function getSectionProducts(): Collection
    {
        return $this->sectionProducts;
    }

    public function addSectionProduct(SectionProduct $sectionProduct): static
    {
        if (!$this->sectionProducts->contains($sectionProduct)) {
            $this->sectionProducts->add($sectionProduct);
            $sectionProduct->setProduct($this);
        }

        return $this;
    }

    public function removeSectionProduct(SectionProduct $sectionProduct): static
    {
        if ($this->sectionProducts->removeElement($sectionProduct)) {
            // set the owning side to null (unless already changed)
            if ($sectionProduct->getProduct() === $this) {
                $sectionProduct->setProduct(null);
            }
        }

        return $this;
    }
}
