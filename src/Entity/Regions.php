<?php

namespace App\Entity;

use App\Repository\RegionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RegionsRepository::class)
 */
class Regions
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom_president;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom_president;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email_president;

    /**
     * @ORM\Column(type="integer")
     */
    private $telephone_president;

    /**
     * @ORM\OneToMany(targetEntity=Associations::class, mappedBy="region")
     */
    private $associations;


    /**
     * @ORM\OneToMany(targetEntity=Users::class, mappedBy="region")
     */
    private $users;


    public function __construct()
    {
        $this->associations = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getNomPresident(): ?string
    {
        return $this->nom_president;
    }

    public function setNomPresident(string $nom_president): self
    {
        $this->nom_president = $nom_president;

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

    public function getTelephonePresident(): ?int
    {
        return $this->telephone_president;
    }

    public function setTelephonePresident(int $telephone_president): self
    {
        $this->telephone_president = $telephone_president;

        return $this;
    }

    /**
     * @return Collection|Associations[]
     */
    public function getAssociations(): Collection
    {
        return $this->associations;
    }

    public function addAssociation(Associations $association): self
    {
        if (!$this->associations->contains($association)) {
            $this->associations[] = $association;
            $association->setRegion($this);
        }

        return $this;
    }

    public function removeAssociation(Associations $association): self
    {
        if ($this->associations->removeElement($association)) {
            // set the owning side to null (unless already changed)
            if ($association->getRegion() === $this) {
                $association->setRegion(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->nom;
    }
}
