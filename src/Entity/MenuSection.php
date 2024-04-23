<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Patch;
use App\Repository\MenuSectionRepository;
use App\Security\ApiSecurityExpressionDirectory;
use App\State\RankedEntityStateProcessor;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MenuSectionRepository::class)]
#[ORM\UniqueConstraint("menu_section_unique", columns: ["menu_id", "section_id"])]
//#[ORM\UniqueConstraint("menu_section_rank_unique", columns: ["menu_id", "rank"])]
#[UniqueEntity(
    fields: ["menu", "section"],
    errorPath: "section",
    message: "Cette section est déjà reliée au menu",
)]
/*#[UniqueEntity(
    fields: ["menu", "rank"],
    errorPath: "rank",
    message: "Ce rang de section est déjà assigné sur ce menu",
)]*/
#[ApiResource(
    operations: [
        new Patch(
            security: ApiSecurityExpressionDirectory::ADMIN_OR_OWNER,
            processor: RankedEntityStateProcessor::class
        )
    ],
    normalizationContext: ["groups" => ["menuSection:read", "menuSection:write"]],
    denormalizationContext: ["groups" => ["menuSection:write"]]
)]
class MenuSection implements OwnedEntityInterface, RankedEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: "doctrine.ulid_generator")]
    #[Groups(["up:section:read", "section:read", "menu:read:get", "menuSection:read"])]
    private ?Ulid $id = null;

    #[ORM\ManyToOne(inversedBy: 'menuSections', fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: false)]
    //#[Assert\NotBlank]
    #[Groups(["up:section:read", "section:read:self", "menuSection:read"])]
    private ?Menu $menu = null;

    #[ORM\OneToOne(inversedBy: 'sectionMenu', cascade: ['persist', 'remove'], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: false)]
    //#[Assert\NotBlank]
    #[Groups(["menu:read:get", "menuSection:read"])]
    private ?Section $section = null;

    #[ORM\Column(options: ["default" => false])]
    #[Groups(["up:section:read", "section:read", "menuSection:write", "menu:read:get"])]
    private ?bool $visible = null;

    #[ORM\Column(options: ["unsigned" => true])]
    #[Assert\Positive(message: "Le rang doit être positif")]
    #[Assert\LessThan(10000, message: "Le rang doit être inférieur à 10000")]
    #[Assert\NotBlank]
    #[Groups(["up:section:read", "section:read", "menuSection:write", "menu:read:get"])]
    private ?int $rank = null;

    public function __construct()
    {
        $this->visible = false;
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;

        return $this;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(Section $section): static
    {
        $this->section = $section;

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
        return $this->getMenu()->getMenuSections();
    }

    public function getMaxParentCollectionRank(): ?int
    {
        return $this->getMenu()?->getMaxSectionRank() ?? null;
    }

    public function getOwner(): ?User
    {
        return $this->getMenu()->getOwner();
    }
}
