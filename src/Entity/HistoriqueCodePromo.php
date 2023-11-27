<?php

namespace App\Entity;

use App\Repository\HistoriqueCodePromoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueCodePromoRepository::class)]
class HistoriqueCodePromo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'codepromo')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'historiqueCodePromo')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CodePromo $codePromo = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbUtilisationUser = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getCodePromo(): ?CodePromo
    {
        return $this->codePromo;
    }

    public function setCodePromo(?CodePromo $codePromo): self
    {
        $this->codePromo = $codePromo;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getNbUtilisationUser(): ?int
    {
        return $this->nbUtilisationUser;
    }

    public function setNbUtilisationUser(?int $nbUtilisationUser): self
    {
        $this->nbUtilisationUser = $nbUtilisationUser;

        return $this;
    }
}
