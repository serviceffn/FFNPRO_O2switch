<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FactureRepository::class)
 * @ORM\Table(name="facture")
 */
class Facture
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

   /**
     * @ORM\Column(name="associationId", type="integer", nullable=true)
     */
    private $associationId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $user_id;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $isPaid = false;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="blob", nullable=true)
     */
    private $pdfContent;

      /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pdfFilename;


    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $notification = false;

    /**
     * @ORM\Column(name="notificationEndDate", type="datetime", nullable=true)
     */
    private $notificationEndDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAssociationId(): ?int
    {
        return $this->associationId;
    }

    public function setAssociationId(int $associationId): self
    {
        $this->associationId = $associationId;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getIsPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(bool $isPaid): self
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPdfContent()
    {
        return $this->pdfContent;
    }

    public function setPdfContent($pdfContent): self
    {
        $this->pdfContent = $pdfContent;

        return $this;
    }


    public function getPdfFilename(): ?string
    {
        return $this->pdfFilename;
    }

    public function setPdfFilename(?string $pdfFilename): self
    {
        $this->pdfFilename = $pdfFilename;

        return $this;
    }


    public function getNotification(): ?bool
    {
        return $this->notification;
    }

    public function setNotification(bool $notification): self
    {
        $this->notification = $notification;

        return $this;
    }

    public function getNotificationEndDate(): ?\DateTimeInterface
    {
        return $this->notificationEndDate;
    }

    public function setNotificationEndDate(?\DateTimeInterface $notificationEndDate): self
    {
        $this->notificationEndDate = $notificationEndDate;

        return $this;
    }
    
}
