<?php

namespace App\Entity;

use App\Repository\CarteFideliteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarteFideliteRepository::class)]
class CarteFidelite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'carteFidelite')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'carteFidelites')]
    private ?Adress $adress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $specialite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $club = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column]
    private ?int $points = null;

    #[ORM\Column]
    private ?int $nombre_achat = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dernier_achat = null;

    #[ORM\Column(nullable: true)]
    private ?int $sommeCompte = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAdress(): ?Adress
    {
        return $this->adress;
    }

    public function setAdress(?Adress $adress): self
    {
        $this->adress = $adress;

        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(?string $specialite): self
    {
        $this->specialite = $specialite;

        return $this;
    }

    public function getClub(): ?string
    {
        return $this->club;
    }

    public function setClub(?string $club): self
    {
        $this->club = $club;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;

        return $this;
    }

    public function getNombreAchat(): ?int
    {
        return $this->nombre_achat;
    }

    public function setNombreAchat(int $nombre_achat): self
    {
        $this->nombre_achat = $nombre_achat;

        return $this;
    }

    
    public function getDernierAchat(): ?\DateTimeImmutable
    {
        return $this->dernier_achat;
    }

    public function setDernierAchat(?\DateTimeImmutable $dernier_achat): self
    {
        $this->dernier_achat = $dernier_achat;
        return $this;
    }

    public function getSommeCompte(): ?int
    {
        return $this->sommeCompte;
    }

    public function setSommeCompte(?int $sommeCompte): self
    {
        $this->sommeCompte = $sommeCompte;

        return $this;
    }
}
