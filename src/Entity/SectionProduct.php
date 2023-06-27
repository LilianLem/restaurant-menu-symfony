<?php

namespace App\Entity;

use App\Repository\SectionProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SectionProductRepository::class)]
#[ORM\UniqueConstraint("section_product_unique", columns: ["section_id", "product_id"])]
#[ORM\UniqueConstraint("section_product_rank_unique", columns: ["section_id", "rank"])]
#[UniqueEntity(
    fields: ["section", "product"],
    errorPath: "product",
    message: "Ce produit est déjà relié à la section",
)]
#[UniqueEntity(
    fields: ["section", "rank"],
    errorPath: "rank",
    message: "Ce rang de produit est déjà assigné sur cette section",
)]
// TODO: also check that the product is not already linked to another section of the same menu (create a custom validation function?)
class SectionProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ["unsigned" => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sectionProducts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Section $section = null;

    #[ORM\ManyToOne(inversedBy: 'sectionProducts', cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Product $product = null;

    #[ORM\Column(options: ["unsigned" => true])]
    #[Assert\Positive(message: "Le rang doit être positif")]
    #[Assert\NotBlank]
    private ?int $rank = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(?Section $section): static
    {
        $this->section = $section;

        return $this;
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

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }
}
