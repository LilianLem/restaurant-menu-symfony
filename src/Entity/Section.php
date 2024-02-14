<?php

namespace App\Entity;

use App\Repository\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SectionRepository::class)]
class Section
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ["unsigned" => true])]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?int $id = null;

    #[ORM\Column(length: 128, nullable: true)]
    #[Assert\Length(max: 128, maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères")]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?string $name = null;

    #[ORM\Column(nullable: true, options: ["unsigned" => true])]
    #[Assert\PositiveOrZero(message: "Le prix ne peut pas être négatif")]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?int $price = null;

    #[ORM\OneToMany(mappedBy: 'section', targetEntity: SectionProduct::class, orphanRemoval: true, cascade: ["persist"])]
    #[Groups(["getRestaurants", "getMenus", "getSections"])]
    private Collection $sectionProducts;

    #[ORM\OneToOne(mappedBy: 'section', cascade: ['persist', 'remove'])]
    #[Groups(["getSections", "getProducts"])]
    private ?MenuSection $sectionMenu = null;

    public function __construct()
    {
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

    public function getMaxProductRank(): int
    {
        if($this->getSectionProducts()->isEmpty()) {
            return 0;
        }

        return $this->getSectionProducts()->reduce(fn(int $maxRank, SectionProduct $sp): int => $sp->getRank() > $maxRank ? $sp->getRank() : $maxRank, 0);
    }
}
