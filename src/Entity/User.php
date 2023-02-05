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


#[ORM\Entity(repositoryClass:UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $token = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $rne = null;

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

    #[ORM\Column(type : Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTime $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $lastVisit = null;

    #[ORM\OneToOne(targetEntity : Fichiersequipes::class, cascade: ['persist'])]
    private ?Fichiersequipes $autorisationphotos = null;


    #[ORM\ManyToOne]
    private ?Rne $rneId=null;

    #[ORM\Column(nullable: true)]
    private ?bool $newsletter;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $roles = [];

    #[ORM\Column(length: 255,  unique: true)]
    private ?string $email = null;


    #[Pure] public function __construct()
    {
        $this->isActive = true;
        $this->roles = ['ROLE_USER'];


    }

    #[Pure] public function __toString(): string
    {
        return $this->prenom . ' ' . $this->getNom();
    }

    /**
     * Get nom
     *
     * @return string|null
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return User
     */
    public function setNom(string $nom): User
    {
        $this->nom = $nom;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }


    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * The public representation of the user (e.g. a username, an email address, etc.)
     *
     * @see UserInterface
     */
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

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string|null $token
     */
    public function setToken(?string $token): void
    {
        $this->token = $token;
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

    /**
     * @see PasswordAuthenticatedUserInterface
     */
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

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
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


    /**
     * @Assert\NotBlank(groups={"registration"})
     * @Assert\Length(max=4096)
     */

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * Get Adresse
     *
     * @return string|null
     */
    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    /**
     * Set adresse
     *
     * @param string|null $adresse
     *
     * @return User
     */
    public function setAdresse(?string $adresse): User
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get ville
     *
     * @return string|null
     */
    public function getVille(): ?string
    {
        return $this->ville;
    }

    /**
     * Set ville
     *
     * @param string|null $ville
     *
     * @return User
     */
    public function setVille(?string $ville): User
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get code
     *
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Set Code
     *
     * @param string|null $code
     *
     * @return User
     */
    public function setCode(?string $code): User
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get
     *
     * @return string|null
     */
    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    /**
     * Set civilite
     *
     * @param string|null $civilite
     *
     * @return User
     */
    public function setCivilite(?string $civilite): User
    {
        $this->civilite = $civilite;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * Set phone
     *
     * @param string|null $phone
     * @return User
     */
    public function setPhone(?string $phone): User
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get rne
     *
     * @return string|null
     */
    public function getRne(): ?string
    {
        return $this->rne;
    }

    /**
     * Set rne
     *
     * @param string|null $rne
     * @return User
     */
    public function setRne(?string $rne): User
    {
        $this->rne = $rne;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string|null
     */
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    /**
     * Set prenom
     *
     * @param string|null $prenom
     *
     * @return User
     */
    public function setPrenom(?string $prenom): User
    {
        $this->prenom = $prenom;

        return $this;
    }

    /*
    * Get createdAt
    */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /*
     * Set updatedAt
     */
    public function setCreatedAt(?Datetime $createdAt): User
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /*
     * Get updatedAt
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /*
     * Set updatedAt
     */
    public function setUpdatedAt(?Datetime $updatedAt): User
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /* Get lastVisit
    */
    public function getLastVisit(): ?DateTime
    {
        return $this->lastVisit;
    }

    /*
     * Set lastVisit
     */
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




    public function getRneId(): ?rne
    {
        return $this->rneId;
    }

    public function setRneId(?rne $rneId): self
    {
        $this->rneId = $rneId;

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


}