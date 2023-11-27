<?php

namespace App\Entity;

use App\Repository\PointFideliteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PointFideliteRepository::class)]
class PointFidelite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $montantPanier = null;

    #[ORM\Column]
    private ?int $point = null;

    #[ORM\Column]
    private ?int $conversionEuroPoints = null;

    #[ORM\Column]
    private ?float $ratioCdePanierEnPoint = null;

    #[ORM\Column]
    private ?float $remise = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontantPanier(): ?float
    {
        return $this->montantPanier;
    }

    public function setMontantPanier(float $montantPanier): self
    {
        $this->montantPanier = $montantPanier;

        return $this;
    }

    public function getPoint(): ?int
    {
        return $this->point;
    }

    public function setPoint(int $point): self
    {
        $this->point = $point;

        return $this;
    }

    public function getConversionEuroPoints(): ?int
    {
        return $this->conversionEuroPoints;
    }

    public function setConversionEuroPoints(int $conversionEuroPoints): self
    {
        $this->conversionEuroPoints = $conversionEuroPoints;

        return $this;
    }

    public function getRatioCdePanierEnPoint(): ?float
    {
        return $this->ratioCdePanierEnPoint;
    }

    public function setRatioCdePanierEnPoint(float $ratioCdePanierEnPoint): self
    {
        $this->ratioCdePanierEnPoint = $ratioCdePanierEnPoint;

        return $this;
    }

    public function getRemise(): ?float
    {
        return $this->remise;
    }

    public function setRemise(float $remise): self
    {
        $this->remise = $remise;

        return $this;
    }
}
