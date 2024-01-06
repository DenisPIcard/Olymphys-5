<?php

namespace App\Entity;

use App\Repository\EquipesRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Pure;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $token = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $uai = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $ville = null;

    #[ORM\Column(length: 11, nullable: true)]
    protected ?string $code = null;

    #[ORM\Column(length: 15, nullable: true)]
    protected ?string $phone = null;

    #[ORM\Column(length: 15, nullable: true)]
    protected ?string $civilite = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    private ?string $plainPassword = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isActive = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $passwordRequestedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTime $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $lastVisit = null;

    #[ORM\OneToOne(targetEntity: Fichiersequipes::class, cascade: ['persist'])]
    private ?Fichiersequipes $autorisationphotos = null;


    #[ORM\ManyToOne]
    private ?Uai $uaiId = null;

    #[ORM\Column(nullable: true)]
    private ?bool $newsletter;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $roles = [];

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\ManyToOne]
    private ?Centrescia $centrecia;


    #[Pure] public function __construct()
    {
        $this->isActive = true;
        $this->roles = ['ROLE_USER'];


    }

    #[Pure] public function __toString(): string
    {
        return $this->prenom . ' ' . $this->getNom();
    }


    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): User
    {
        $this->nom = $nom;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getUsername(): ?string
    {
        return $this->username;
    }


    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail($email): User
    {
        $this->email = $email;
        return $this;
    }


    public function getToken(): ?string
    {
        return $this->token;
    }


    public function setToken(?string $token): void
    {
        $this->token = $token;
    }


    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {

        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        foreach ($roles as $role) {
            if (!str_starts_with($role, 'ROLE_')) {
                throw new InvalidArgumentException("Chaque rôle doit commencer par 'ROLE_'");
            }
        }
        $this->roles = $roles;
        return $this;
    }


    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): User
    {
        $this->isActive = $isActive;
        return $this;
    }


    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getPasswordRequestedAt(): ?DateTime
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(?DateTime $passwordRequestedAt): User
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
        return $this;
    }


    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }


    public function setAdresse(?string $adresse): User
    {
        $this->adresse = $adresse;

        return $this;
    }


    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): User
    {
        $this->ville = $ville;

        return $this;
    }


    public function getCode(): ?string
    {
        return $this->code;
    }


    public function setCode(?string $code): User
    {
        $this->code = $code;

        return $this;
    }

    public function getCivilite(): ?string
    {
        return $this->civilite;
    }


    public function setCivilite(?string $civilite): User
    {
        $this->civilite = $civilite;

        return $this;
    }


    public function getPhone(): ?string
    {
        return $this->phone;
    }


    public function setPhone(?string $phone): User
    {
        $this->phone = $phone;

        return $this;
    }


    public function getUai(): ?string
    {
        return $this->uai;
    }


    public function setUai(?string $uai): User
    {
        $this->uai = $uai;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }


    public function setPrenom(?string $prenom): User
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?Datetime $createdAt): User
    {
        $this->createdAt = $createdAt;
        return $this;
    }


    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }


    public function setUpdatedAt(?Datetime $updatedAt): User
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }


    public function getLastVisit(): ?DateTime
    {
        return $this->lastVisit;
    }


    public function setLastVisit(?DateTime $lastVisit): User
    {
        $this->lastVisit = $lastVisit;
        return $this;
    }

    public function getAutorisationphotos(): ?Fichiersequipes
    {
        return $this->autorisationphotos;
    }


    public function setAutorisationphotos(?Fichiersequipes $autorisation): User
    {
        $this->autorisationphotos = $autorisation;

        return $this;
    }

    public function getNomPrenom(): ?string
    {
        return $this->nom . ' ' . $this->prenom;

    }

    public function getPrenomNom(): ?string
    {
        return $this->prenom . ' ' . $this->nom;

    }


    public function getUaiId(): ?Uai
    {
        return $this->uaiId;
    }

    public function setUaiId(?Uai $uaiId): self
    {
        $this->uaiId = $uaiId;

        return $this;
    }

    public function getNewsletter(): ?bool
    {
        return $this->newsletter;
    }

    public function setNewsletter(?bool $newsletter): self
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    #[ArrayShape(['id' => "int|null",
        'username' => "null|string",
        'password' => "null|string",
        'isActive' => "bool|null"])] public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'password' => $this->password,
            'isActive' => $this->isActive,
        ];
    }

    public function __unserialize(array $data): void
    {

        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->isActive = $data['isActive'];

    }

    public function getCentrecia(): ?Centrescia
    {
        return $this->centrecia;
    }

    public function setCentrecia(?Centrescia $centrecia): self
    {
        $this->centrecia = $centrecia;

        return $this;
    }


}