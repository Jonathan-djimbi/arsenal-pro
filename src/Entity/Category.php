<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Produit::class)]
    private Collection $produits;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Famille::class)]
    private Collection $familles;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Calibre::class)]
    private Collection $calibres;



    public function __construct()
    {
        $this->produits = new ArrayCollection();
        $this->familles = new ArrayCollection();
        $this->calibres = new ArrayCollection();
    }
    public function __toString()
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
            $produit->setCategory($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getCategory() === $this) {
                $produit->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Famille>
     */
    public function getFamilles(): Collection
    {
        return $this->familles;
    }

    public function addFamille(Famille $famille): self
    {
        if (!$this->familles->contains($famille)) {
            $this->familles->add($famille);
            $famille->setCategory($this);
        }

        return $this;
    }

    public function removeFamille(Famille $famille): self
    {
        if ($this->familles->removeElement($famille)) {
            // set the owning side to null (unless already changed)
            if ($famille->getCategory() === $this) {
                $famille->setCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Calibre>
     */
    public function getCalibres(): Collection
    {
        return $this->calibres;
    }

    public function addCalibre(Calibre $calibre): self
    {
        if (!$this->calibres->contains($calibre)) {
            $this->calibres->add($calibre);
            $calibre->setCategory($this);
        }

        return $this;
    }

    public function removeCalibre(Calibre $calibre): self
    {
        if ($this->calibres->removeElement($calibre)) {
            // set the owning side to null (unless already changed)
            if ($calibre->getCategory() === $this) {
                $calibre->setCategory(null);
            }
        }

        return $this;
    }

}
