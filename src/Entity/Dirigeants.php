<?php

namespace App\Entity;

use App\Repository\DirigeantsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DirigeantsRepository::class)
 */
class Dirigeants
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Associations::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $centre_emetteur;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $president;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $vice_president;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tresorier;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $secretaire;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCentreEmetteur(): ?Associations
    {
        return $this->centre_emetteur;
    }

    public function setCentreEmetteur(?Associations $centre_emetteur): self
    {
        $this->centre_emetteur = $centre_emetteur;

        return $this;
    }

    public function getPresident(): ?string
    {
        return $this->president;
    }

    public function setPresident(string $president): self
    {
        $this->president = $president;

        return $this;
    }

    public function getVicePresident(): ?string
    {
        return $this->vice_president;
    }

    public function setVicePresident(string $vice_president): self
    {
        $this->vice_president = $vice_president;

        return $this;
    }

    public function getTresorier(): ?string
    {
        return $this->tresorier;
    }

    public function setTresorier(string $tresorier): self
    {
        $this->tresorier = $tresorier;

        return $this;
    }

    public function getSecretaire(): ?string
    {
        return $this->secretaire;
    }

    public function setSecretaire(string $secretaire): self
    {
        $this->secretaire = $secretaire;

        return $this;
    }
}
