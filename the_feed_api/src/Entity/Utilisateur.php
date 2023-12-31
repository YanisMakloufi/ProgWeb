<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\UtilisateurRepository;
use App\State\UtilisateurProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(operations: [
    new Get(),
    new Delete(security: "is_granted('ROLE_USER') and object.getOwner() == user"),
    new Post(denormalizationContext: ["groups" => ["utilisateur:create"]], validationContext: ["groups" => ["Default", "utilisateur:create"]], processor: UtilisateurProcessor::class),
    new GetCollection(),
    new Patch(denormalizationContext: ["groups" => ["utilisateur:update"]],
        security: "is_granted('ROLE_USER') and object.getOwner() == user",
        validationContext: ["groups" => ["Default", "utilisateur:update"]])],
    normalizationContext: ["groups" => ["utilisateur:read"]])]
#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['utilisateur:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(min: 4, max: 20, minMessage: 'Il faut au moins 4 caractères!', maxMessage: 'Il faut moins de 20 caractères')]
    #[Groups(['utilisateur:read', "utilisateur:create"])]
    private ?string $login = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[ApiProperty(writable: false, readable: false)]
    private ?string $password = null;

    #[Assert\Regex(pattern: "^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)[a-zA-Z\\d]{8,30}$^")]
    #[Assert\NotNull(groups: ['utilisateur:create'])]
    #[Assert\NotBlank(groups: ['utilisateur:create'])]
    #[Assert\Length(min: 8, max: 30)]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\Email()]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['utilisateur:read', 'publication:read', "utilisateur:create", "utilisateur:update"])]
    private ?string $adresseEmail = null;

    #[ORM\Column(options: ["default" => false])]
    #[ApiProperty(writable: false)]
    #[Groups(['utilisateur:read', 'publication:read', "utilisateur:create", "utilisateur:update"])]
    private ?bool $premium = false;

    #[ORM\OneToMany(mappedBy: 'auteur', targetEntity: Publication::class, orphanRemoval: true)]
    private Collection $publications;

    public function __construct()
    {
        $this->publications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->login;
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

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getAdresseEmail(): ?string
    {
        return $this->adresseEmail;
    }

    public function setAdresseEmail(string $adresseEmail): static
    {
        $this->adresseEmail = $adresseEmail;

        return $this;
    }

    public function isPremium(): ?bool
    {
        return $this->premium;
    }

    public function setPremium(bool $premium): static
    {
        $this->premium = $premium;

        return $this;
    }

    /**
     * @return Collection<int, Publication>
     */
    public function getPublications(): Collection
    {
        return $this->publications;
    }

    public function addPublication(Publication $publication): static
    {
        if (!$this->publications->contains($publication)) {
            $this->publications->add($publication);
            $publication->setAuteur($this);
        }

        return $this;
    }

    public function removePublication(Publication $publication): static
    {
        if ($this->publications->removeElement($publication)) {
            // set the owning side to null (unless already changed)
            if ($publication->getAuteur() === $this) {
                $publication->setAuteur(null);
            }
        }

        return $this;
    }
}
