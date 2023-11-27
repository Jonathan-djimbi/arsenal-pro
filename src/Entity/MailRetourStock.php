<?php

namespace App\Entity;

use App\Repository\MailRetourStockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MailRetourStockRepository::class)]
class MailRetourStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $email = null;

    #[ORM\ManyToOne(inversedBy: 'produitMailStockIn')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column]
    private ?bool $mail_etat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function isMailEtat(): ?bool
    {
        return $this->mail_etat;
    }

    public function setMailEtat(bool $mail_etat): self
    {
        $this->mail_etat = $mail_etat;

        return $this;
    }
}
