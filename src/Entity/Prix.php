<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PrixRepository;

/**
 * @ORM\Entity(repositoryClass=PrixRepository::class)
 * @ORM\Table(name="Prix") // Spécifiez ici le nom de la table
 */
class Prix
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
    private $type_licence;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $prix;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeLicence(): ?string
    {
        return $this->type_licence;
    }

    public function getPrix(): float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
