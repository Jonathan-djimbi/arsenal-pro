<?php

namespace App\Entity;

use App\Repository\CalibreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CalibreRepository::class)]
class Calibre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $calibre = null;

    #[ORM\OneToMany(mappedBy: 'calibres', targetEntity: Produit::class)]
    private Collection $produit;

    #[ORM\ManyToOne(inversedBy: 'calibres')]
    private ?Category $category = null;

    public function __construct()
    {
        $this->produit = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCalibre(): ?string
    {
        return $this->calibre;
    }

    public function setCalibre(string $calibre): self
    {
        $this->calibre = $calibre;

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduit(): Collection
    {
        return $this->produit;
    }

    public function addProduit(Produit $produit): self
    {
        if (!$this->produit->contains($produit)) {
            $this->produit->add($produit);
            $produit->setCalibres($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): self
    {
        if ($this->produit->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getCalibres() === $this) {
                $produit->setCalibres(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
    
    public function __toString()
    {
        return $this->getCalibre();
    }
}
