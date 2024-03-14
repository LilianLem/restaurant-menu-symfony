<?php

namespace App\Entity;

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
use App\Repository\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SectionRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: 'is_granted("ROLE_ADMIN") or object.getOwner() === user' // TODO: allow users to get only sections on menus on owned restaurants
        ),
        new Get(
            normalizationContext: ["groups" => ["section:read", "section:read:self", "section:read:get", "product:read", "up:section:read", "up:menu:read", "up:restaurant:read"]],
            security: 'object.getOwner() === user or object.isPublic()'
        ),
        new Post(
            security: 'is_granted("ROLE_USER")' // TODO: force creating on a menu of a self-owned restaurant
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN") or object.getOwner() === user' // TODO: extra security to prevent deleting by mistake (user confirmation)
        ),
        new Patch(
            denormalizationContext: ["groups" => ["section:write", "section:write:update"]],
            security: 'is_granted("ROLE_ADMIN") or object.getOwner() === user'
        ),
        new Put(
            denormalizationContext: ["groups" => ["section:write", "section:write:update"]],
            security: 'is_granted("ROLE_ADMIN") or object.getOwner() === user'
        )
    ],
    normalizationContext: ["groups" => ["section:read", "section:read:self"]],
    denormalizationContext: ["groups" => ["section:write"]]
)]
#[ApiFilter(SearchFilter::class, properties: [
    "sectionProducts.product" => SearchFilter::STRATEGY_EXACT,
    "sectionMenu.menu" => SearchFilter::STRATEGY_EXACT,
    "sectionMenu.menu.menuRestaurants.restaurant" => SearchFilter::STRATEGY_EXACT,
    "sectionMenu.menu.menuRestaurants.restaurant.owner" => SearchFilter::STRATEGY_EXACT
])]
class Section
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: "doctrine.ulid_generator")]
    #[Groups(["section:read", "up:product:read"])]
    private ?Ulid $id = null;

    #[ORM\Column(length: 128, nullable: true)]
    #[Assert\Length(max: 128, maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères")]
    #[Groups(["section:read", "section:write", "up:product:read"])]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilter::STRATEGY_PARTIAL)]
    private ?string $name = null;

    #[ORM\Column(nullable: true, options: ["unsigned" => true])]
    #[Assert\PositiveOrZero(message: "Le prix ne peut pas être négatif")]
    #[Groups(["section:read", "section:write", "up:product:read"])]
    #[ApiFilter(RangeFilter::class)]
    #[ApiFilter(ExistsFilter::class)]
    private ?int $price = null;

    #[ORM\OneToMany(mappedBy: 'section', targetEntity: SectionProduct::class, orphanRemoval: true, cascade: ["persist"])]
    #[Groups(["section:read", "section:write:update"])]
    #[ApiFilter(ExistsFilter::class)]
    private Collection $sectionProducts;

    #[ORM\OneToOne(mappedBy: 'section', cascade: ['persist', 'remove'])]
    #[Groups(["section:read:self", "section:write", "up:section:read"])]
    private ?MenuSection $sectionMenu = null;

    public function __construct()
    {
        $this->sectionProducts = new ArrayCollection();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
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
            $sectionProduct->setSection($this);
        }

        return $this;
    }

    public function removeSectionProduct(SectionProduct $sectionProduct): static
    {
        if ($this->sectionProducts->removeElement($sectionProduct)) {
            // set the owning side to null (unless already changed)
            if ($sectionProduct->getSection() === $this) {
                $sectionProduct->setSection(null);
            }
        }

        return $this;
    }

    public function getSectionMenu(): ?MenuSection
    {
        return $this->sectionMenu;
    }

    public function setSectionMenu(MenuSection $sectionMenu): static
    {
        // set the owning side of the relation if necessary
        if ($sectionMenu->getSection() !== $this) {
            $sectionMenu->setSection($this);
        }

        $this->sectionMenu = $sectionMenu;

        return $this;
    }

    #[Groups(["section:read:get"])]
    public function getMaxProductRank(): int
    {
        if($this->getSectionProducts()->isEmpty()) {
            return 0;
        }

        return $this->getSectionProducts()->reduce(fn(int $maxRank, SectionProduct $sp): int => $sp->getRank() > $maxRank ? $sp->getRank() : $maxRank, 0);
    }

    public function isPublic(): bool
    {
        return $this->getSectionMenu()->getMenu()->isPublic() && $this->getSectionMenu()->isVisible();
    }

    public function getOwner(): ?User
    {
        return $this->getSectionMenu()->getMenu()->getOwner();
    }
}
