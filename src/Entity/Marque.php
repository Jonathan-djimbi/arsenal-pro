<?php

namespace App\Entity;

use App\Repository\MarqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MarqueRepository::class)]
class Marque
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\OneToMany(mappedBy: 'marque', targetEntity: Produit::class)]
    private Collection $produits;

    #[ORM\OneToMany(mappedBy: 'marques', targetEntity: RemiseGroupe::class)]
    private Collection $remiseGroupes;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
        $this->remiseGroupes = new ArrayCollection();
    }
    public function __toString() //recherche par nom pour le search et searchtype
    {
       return $this->getName();
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

    
    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): self
    {
        $this->photo = $photo;

        return $this;
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
            $produit->setMarque($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getMarque() === $this) {
                $produit->setMarque(null);
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
            $remiseGroupe->setMarques($this);
        }

        return $this;
    }

    public function removeRemiseGroupe(RemiseGroupe $remiseGroupe): self
    {
        if ($this->remiseGroupes->removeElement($remiseGroupe)) {
            // set the owning side to null (unless already changed)
            if ($remiseGroupe->getMarques() === $this) {
                $remiseGroupe->setMarques(null);
            }
        }

        return $this;
    }
}
