<?php

namespace App\Entity;

use App\Repository\FournisseursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FournisseursRepository::class)]
class Fournisseurs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'fournisseurs', targetEntity: Produit::class)]
    private Collection $produits;

    #[ORM\OneToMany(mappedBy: 'fournisseur', targetEntity: RemiseGroupe::class)]
    private Collection $remiseGroupes;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
        $this->remiseGroupes = new ArrayCollection();
    }

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

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): self
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setFournisseurs($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getFournisseurs() === $this) {
                $produit->setFournisseurs(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RemiseGroupe>
     */
    public function getRemiseGroupes(): Collection
    {
        return $this->remiseGroupes;
    }

    public function addRemiseGroupe(RemiseGroupe $remiseGroupe): self
    {
        if (!$this->remiseGroupes->contains($remiseGroupe)) {
            $this->remiseGroupes->add($remiseGroupe);
            $remiseGroupe->setFournisseur($this);
        }

        return $this;
    }

    public function removeRemiseGroupe(RemiseGroupe $remiseGroupe): self
    {
        if ($this->remiseGroupes->removeElement($remiseGroupe)) {
            // set the owning side to null (unless already changed)
            if ($remiseGroupe->getFournisseur() === $this) {
                $remiseGroupe->setFournisseur(null);
            }
        }

        return $this;
    }


}
