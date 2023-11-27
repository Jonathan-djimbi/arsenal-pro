<?php

namespace App\Entity;

use App\Repository\RemiseGroupeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RemiseGroupeRepository::class)]
class RemiseGroupe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $remise = 0;

    #[ORM\ManyToOne(inversedBy: 'remiseGroupes')]
    private ?Fournisseurs $fournisseur = null;

    #[ORM\ManyToOne(inversedBy: 'remiseGroupes')]
    private ?SubCategory $subCategories = null;

    #[ORM\Column]
    private ?int $priority = null;

    #[ORM\ManyToOne(inversedBy: 'remiseGroupes')]
    private ?Marque $marques = null;

    #[ORM\Column(nullable: true, type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $faitLe = null;

    #[ORM\Column]
    private ?bool $desactive = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRemise(): float
    {
        return $this->remise;
    }

    public function setRemise(float $remise): self
    {
        $this->remise = $remise;

        return $this;
    }

    public function getFournisseur(): ?Fournisseurs
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?Fournisseurs $fournisseur): self
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }

    public function getSubCategories(): ?SubCategory
    {
        return $this->subCategories;
    }

    public function setSubCategories(?SubCategory $subCategories): self
    {
        $this->subCategories = $subCategories;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getMarques(): ?Marque
    {
        return $this->marques;
    }

    public function setMarques(?Marque $marques): self
    {
        $this->marques = $marques;

        return $this;
    }

    public function getFaitLe(): ?\DateTimeInterface
    {
        return $this->faitLe;
    }

    public function setFaitLe(?\DateTimeInterface $faitLe): self
    {
        $this->faitLe = $faitLe;

        return $this;
    }

    public function isDesactive(): ?bool
    {
        return $this->desactive;
    }

    public function setDesactive(bool $desactive): self
    {
        $this->desactive = $desactive;

        return $this;
    }

}
