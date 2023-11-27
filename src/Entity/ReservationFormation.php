<?php

namespace App\Entity;

use App\Repository\ReservationFormationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationFormationRepository::class)]
class ReservationFormation //DE LA MERDE VICTOR PUTAIN
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    private ?string $price = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $description_un = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $description_deux = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $publie_le = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDescriptionUn(): ?string
    {
        return $this->description_un;
    }

    public function setDescriptionUn(?string $description_un): self
    {
        $this->description_un = $description_un;

        return $this;
    }

    public function getDescriptionDeux(): ?string
    {
        return $this->description_deux;
    }

    public function setDescriptionDeux(?string $description_deux): self
    {
        $this->description_deux = $description_deux;

        return $this;
    }

    public function getPublieLe(): ?\DateTimeInterface
    {
        return $this->publie_le;
    }

    public function setPublieLe(\DateTimeInterface $publie_le): self
    {
        $this->publie_le = $publie_le;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
