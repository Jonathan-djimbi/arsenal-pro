<?php

namespace App\Entity;

use App\Repository\CartDatabaseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartDatabaseRepository::class)]
class CartDatabase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cartDatabases')]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $timestamps = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $cart = [];

    #[ORM\Column]
    private ?bool $relance = null;

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

    public function getTimestamps(): ?\DateTimeInterface
    {
        return $this->timestamps;
    }

    public function setTimestamps(?\DateTimeInterface $timestamps): self
    {
        $this->timestamps = $timestamps;

        return $this;
    }

    public function getCart(): array
    {
        return $this->cart;
    }

    public function setCart(?array $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    public function isRelance(): ?bool
    {
        return $this->relance;
    }

    public function setRelance(bool $relance): self
    {
        $this->relance = $relance;

        return $this;
    }
}
