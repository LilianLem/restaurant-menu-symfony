<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity("email", message: "Une erreur est survenue dans la création du compte. Si vous en avez déjà un, cliquez sur Connexion")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ["unsigned" => true])]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Length(max: 180, maxMessage: "L'adresse e-mail ne peut pas dépasser {{ limit }} caractères")]
    #[Assert\Email(message: "L'adresse e-mail renseignée n'est pas valide")]
    #[Assert\NotBlank(message: "L'adresse e-mail est obligatoire")]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private array $roles = [];

    // TODO: check if validators are checked correctly, and if NotBlank validator is not blocking everything
    #[Assert\PasswordStrength(["message" => "Ce mot de passe est trop vulnérable. Veuillez choisir un mot de passe plus sécurisé."])]
    #[Assert\NotCompromisedPassword(message: "Ce mot de passe est présent dans une fuite de données connue. Veuillez en choisir un autre.", skipOnError: true)]
    #[Assert\Length(min: 12, max: 128, minMessage: "Ce mot de passe est trop court, il doit compter au moins {{ limit }} caractères", maxMessage: "Ce mot de passe est trop long, il ne doit pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank(message: "Un mot de passe est obligatoire", groups: ["auth"])]
    protected ?string $plainPassword = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Restaurant::class, orphanRemoval: true, cascade: ["persist"])]
    private Collection $restaurants;

    #[ORM\Column(options: ["default" => true])]
    #[Groups(["getRestaurants", "getMenus", "getSections", "getProducts"])]
    private ?bool $enabled = null;

    public function __construct()
    {
        $this->restaurants = new ArrayCollection();
        $this->enabled = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Restaurant>
     */
    public function getRestaurants(): Collection
    {
        return $this->restaurants;
    }

    public function addRestaurant(Restaurant $restaurant): static
    {
        if (!$this->restaurants->contains($restaurant)) {
            $this->restaurants->add($restaurant);
            $restaurant->setOwner($this);
        }

        return $this;
    }

    public function removeRestaurant(Restaurant $restaurant): static
    {
        if ($this->restaurants->removeElement($restaurant)) {
            // set the owning side to null (unless already changed)
            if ($restaurant->getOwner() === $this) {
                $restaurant->setOwner(null);
            }
        }

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }
}
