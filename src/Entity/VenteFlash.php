<?php

namespace App\Entity;

use App\Repository\VenteFlashRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VenteFlashRepository::class)]
class VenteFlash
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'produitsFlash')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $pid = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $temps = null;

    #[ORM\Column]
    private ?bool $isAffiche = null;

    #[ORM\Column]
    private ?float $newPrice = null;

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

    public function getTemps(): ?\DateTimeInterface
    {
        return $this->temps;
    }

    public function setTemps(\DateTimeInterface $temps): self
    {
        $this->temps = $temps;

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

    public function getNewPrice(): ?float
    {
        return $this->newPrice;
    }

    public function setNewPrice(float $newPrice): self
    {
        $this->newPrice = $newPrice;

        return $this;
    }
}
