<?php

namespace App\Entity;

use App\Repository\BourseArmesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BourseArmesRepository::class)]
class BourseArmes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bourseArmes')]
    #[ORM\JoinColumn(unique: true)]
    private ?Produit $pid = null;

    #[ORM\Column]
    private ?int $quantiteMax = null;

    #[ORM\Column]
    private ?bool $isAffiche = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $prixArray = [];

    #[ORM\Column]
    private ?float $prixFinal = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateLimite = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $quantiteArray = [];

    #[ORM\Column]
    private ?float $prixTempsReel = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPid(): ?Produit
    {
        return $this->pid;
    }

    public function setPid(?Produit $pid): self
    {
        $this->pid = $pid;

        return $this;
    }

    public function getQuantiteMax(): ?int
    {
        return $this->quantiteMax;
    }

    public function setQuantiteMax(int $quantiteMax): self
    {
        $this->quantiteMax = $quantiteMax;

        return $this;
    }

    public function isAffiche(): ?bool
    {
        return $this->isAffiche;
    }

    public function setIsAffiche(bool $isAffiche): self
    {
        $this->isAffiche = $isAffiche;

        return $this;
    }

    public function getPrixArray(): array
    {
        return $this->prixArray;
    }

    public function setPrixArray(array $prixArray): self
    {
        $this->prixArray = $prixArray;

        return $this;
    }

    public function getPrixFinal(): ?float
    {
        return $this->prixFinal;
    }

    public function setPrixFinal(float $prixFinal): self
    {
        $this->prixFinal = $prixFinal;

        return $this;
    }

    public function getDateLimite(): ?\DateTimeInterface
    {
        return $this->dateLimite;
    }

    public function setDateLimite(\DateTimeInterface $dateLimite): self
    {
        $this->dateLimite = $dateLimite;

        return $this;
    }

    public function getQuantiteArray(): array
    {
        return $this->quantiteArray;
    }

    public function setQuantiteArray(?array $quantiteArray): self
    {
        $this->quantiteArray = $quantiteArray;

        return $this;
    }

    public function getPrixTempsReel(): ?float
    {
        return $this->prixTempsReel;
    }

    public function setPrixTempsReel(float $prixTempsReel): self
    {
        $this->prixTempsReel = $prixTempsReel;

        return $this;
    }
}
