<?php

namespace App\Entity;

use App\Repository\AssociationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=AssociationsRepository::class)
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 * @ORM\Table(name="Associations", indexes={@ORM\Index(columns={"nom"}, flags={"fulltext"})})
 */
class Associations implements UserInterface
{


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $adresse;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $zip;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ville;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pays;

    /**
     * @ORM\ManyToOne(targetEntity=Regions::class, inversedBy="associations")
     */
    private $region;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom_presidebt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom_president;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email_president;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email_assoc;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $telephone_president;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $telephone_assoc;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $initiale;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_active;

    /**
     * @ORM\OneToMany(targetEntity=Users::class, mappedBy="centre_emetteur")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=Images::class, mappedBy="associations", orphanRemoval=true, cascade={"persist"})
     */
    private $images;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $dirigeant_president;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $dirigeant_vice_president;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $dirigeant_tresorier;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $dirigeant_secretaire;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;



    // /**
    //  * @ORM\Column(type="boolean")
    //  */
    // private $agree_terms;



    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->images = new ArrayCollection();

    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmailAssoc(): ?string
    {
        return $this->email_assoc;
    }

    public function setEmailAssoc(?string $email_assoc): self
    {
        $this->email_assoc = $email_assoc;

        return $this;
    }

    public function getInitiale(): ?string
    {
        return $this->initiale;
    }

    public function setInitiale(string $initiale): self
    {
        $this->initiale = $initiale;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
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

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
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

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = strtoupper($nom);

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): self
    {
        $this->pays = $pays;

        return $this;
    }

    public function getRegion(): ?Regions
    {
        return $this->region;
    }

    public function setRegion(?Regions $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getNomPresidebt(): ?string
    {
        return $this->nom_presidebt;
    }

    public function setNomPresidebt(string $nom_presidebt): self
    {
        $this->nom_presidebt = $nom_presidebt;

        return $this;
    }

    public function getPrenomPresident(): ?string
    {
        return $this->prenom_president;
    }

    public function setPrenomPresident(string $prenom_president): self
    {
        $this->prenom_president = $prenom_president;

        return $this;
    }

    public function getEmailPresident(): ?string
    {
        return $this->email_president;
    }

    public function setEmailPresident(string $email_president): self
    {
        $this->email_president = $email_president;

        return $this;
    }

    public function getTelephonePresident(): ?string
    {
        return $this->telephone_president;
    }

    public function setTelephonePresident(string $telephone_president): self
    {
        $this->telephone_president = $telephone_president;

        return $this;
    }

    public function getTelephoneAssoc(): ?string
    {
        return $this->telephone_assoc;
    }

    public function setTelephoneAssoc(string $telephone_assoc): self
    {
        $this->telephone_assoc = $telephone_assoc;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): self
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getDirigeantPresident(): ?string
    {
        return $this->dirigeant_president;
    }

    public function setDirigeantPresident(string $dirigeant_president): self
    {
        $this->dirigeant_president = $dirigeant_president;

        return $this;
    }

    public function getDirigeantVicePresident(): ?string
    {
        return $this->dirigeant_vice_president;
    }

    public function setDirigeantVicePresident(string $dirigeant_vice_president): self
    {
        $this->dirigeant_vice_president = $dirigeant_vice_president;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getDirigeantTresorier(): ?string
    {
        return $this->dirigeant_tresorier;
    }

    public function setDirigeantTresorier(string $dirigeant_tresorier): self
    {
        $this->dirigeant_tresorier = $dirigeant_tresorier;

        return $this;
    }

    public function getDirigeantSecretaire(): ?string
    {
        return $this->dirigeant_secretaire;
    }

    public function setDirigeantSecretaire(string $dirigeant_secretaire): self
    {
        $this->dirigeant_secretaire = $dirigeant_secretaire;

        return $this;
    }


    // public function getAgreeTerms(): ?bool
    // {
    //     return $this->agree_terms;
    // }

    // public function setAgreeTerms(bool $agree_terms): self
    // {
    //     $this->agree_terms = $agree_terms;

    //     return $this;
    // }

    /**
     * @return Collection|Users[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(Users $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setCentreEmetteur($this);
        }

        return $this;
    }

    public function removeUser(Users $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCentreEmetteur() === $this) {
                $user->setCentreEmetteur(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->nom;
    }

    /**
     * @return Collection|Images[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Images $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setAssociations($this);
        }

        return $this;
    }

    public function removeImage(Images $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getAssociations() === $this) {
                $image->setAssociations(null);
            }
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $affiliation_number;


    public function getAffiliationNumber(): ?string
    {
        return $this->affiliation_number;
    }

    public function setAffiliationNumber(string $affiliation_number): self
    {
        $this->affiliationNumber = $affiliation_number;

        return $this;
    }

}
