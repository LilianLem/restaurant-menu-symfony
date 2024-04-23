<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\SectionProductRepository;
use App\Security\ApiSecurityExpressionDirectory;
use App\State\RankedEntityStateProcessor;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SectionProductRepository::class)]
#[ORM\UniqueConstraint("section_product_unique", columns: ["section_id", "product_id"])]
//#[ORM\UniqueConstraint("section_product_rank_unique", columns: ["section_id", "rank"])]
#[UniqueEntity(
    fields: ["section", "product"],
    errorPath: "product",
    message: "Ce produit est déjà relié à la section",
)]
/*#[UniqueEntity(
    fields: ["section", "rank"],
    errorPath: "rank",
    message: "Ce rang de produit est déjà assigné sur cette section",
)]*/
#[ApiResource(
    operations: [
        new Post(
            denormalizationContext: ["groups" => ["sectionProduct:write", "sectionProduct:write:post"]],
            security: ApiSecurityExpressionDirectory::LOGGED_USER,
            processor: RankedEntityStateProcessor::class
        ),
        new Delete(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER // TODO: extra security to prevent deleting by mistake (user confirmation)
        ),
        new Patch(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER,
            processor: RankedEntityStateProcessor::class
        )
    ],
    normalizationContext: ["groups" => ["sectionProduct:read", "sectionProduct:write", "sectionProduct:write:post"]],
    denormalizationContext: ["groups" => ["sectionProduct:write"]]
)]
class SectionProduct implements OwnedEntityInterface, RankedEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: "doctrine.ulid_generator")]
    #[Groups(["up:product:read", "product:read", "section:read:get", "sectionProduct:read"])]
    private ?Ulid $id = null;

    #[ORM\ManyToOne(inversedBy: 'sectionProducts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Une section doit être renseignée pour pouvoir y lier un produit")]
    #[AppAssert\IsSelfOwned(options: ["message" => "Cette section ne vous appartient pas"])]
    #[Groups(["up:product:read", "product:read:self", "sectionProduct:write:post"])]
    private ?Section $section = null;

    #[ORM\ManyToOne(inversedBy: 'productSections', cascade: ["persist", "detach"])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Un produit doit être renseigné pour pouvoir y lier une section")]
    #[AppAssert\IsSelfOwned(options: ["message" => "Ce produit ne vous appartient pas"])]
    #[Groups(["section:read:get", "sectionProduct:write:post"])]
    private ?Product $product = null;

    #[ORM\Column(options: ["default" => true])]
    #[Groups(["up:product:read", "product:read", "sectionProduct:write", "section:read:get"])]
    private ?bool $visible = null;

    #[ORM\Column(options: ["unsigned" => true])]
    #[Assert\Positive(message: "Le rang doit être positif")]
    #[Assert\LessThan(10000, message: "Le rang doit être inférieur à 10000")]
    #[Assert\NotBlank] // TODO: autoset rank if empty
    #[Groups(["up:product:read", "product:read", "sectionProduct:write", "section:read:get"])]
    private ?int $rank = null;

    public function __construct()
    {
        $this->visible = true;
    }

    public function getId(): ?Ulid
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

    /** @return Collection<static> */
    public function getSiblings(): Collection
    {
        return $this->getSection()->getSectionProducts();
    }

    public function getMaxParentCollectionRank(): ?int
    {
        return $this->getSection()?->getMaxProductRank() ?? null;
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

    public function getOwner(): ?User
    {
        return $this->getSection()->getOwner();
    }
}
