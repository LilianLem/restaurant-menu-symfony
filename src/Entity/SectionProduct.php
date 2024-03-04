<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Patch;
use App\Repository\SectionProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
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
#[ApiResource(
    operations: [new Patch()],
    denormalizationContext: ["groups" => ["sectionProduct:write"]]
)]
class SectionProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ["unsigned" => true])]
    #[Groups(["up:product:read", "product:read", "section:read:get"])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sectionProducts')]
    #[ORM\JoinColumn(nullable: false)]
    //#[Assert\NotBlank]
    #[Groups(["up:product:read", "product:read:self"])]
    private ?Section $section = null;

    #[ORM\ManyToOne(inversedBy: 'sectionProducts', cascade: ["persist", "detach"])]
    #[ORM\JoinColumn(nullable: false)]
    //#[Assert\NotBlank]
    #[Groups(["section:read:get"])]
    private ?Product $product = null;

    #[ORM\Column(options: ["unsigned" => true])]
    #[Assert\Positive(message: "Le rang doit être positif")]
    #[Assert\NotBlank]
    #[Groups(["up:product:read", "product:read", "sectionProduct:write", "section:read:get"])]
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
