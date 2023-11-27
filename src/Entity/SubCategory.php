<?php

namespace App\Entity;

use App\Repository\SubCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubCategoryRepository::class)]
class SubCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'SubCategory', targetEntity: Produit::class)]
    private Collection $produits;

    #[ORM\OneToMany(mappedBy: 'subCategory', targetEntity: MenuCategories::class)]
    private Collection $menuCategories;

    #[ORM\OneToMany(mappedBy: 'subCategories', targetEntity: RemiseGroupe::class)]
    private Collection $remiseGroupes;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
        $this->menuCategories = new ArrayCollection();
        $this->remiseGroupes = new ArrayCollection();
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
            $produit->setSubCategory($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getSubCategory() === $this) {
                $produit->setSubCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MenuCategories>
     */
    public function getMenuCategories(): Collection
    {
        return $this->menuCategories;
    }

    public function addMenuCategory(MenuCategories $menuCategory): self
    {
        if (!$this->menuCategories->contains($menuCategory)) {
            $this->menuCategories->add($menuCategory);
            $menuCategory->setSubCategory($this);
        }

        return $this;
    }

    public function removeMenuCategory(MenuCategories $menuCategory): self
    {
        if ($this->menuCategories->removeElement($menuCategory)) {
            // set the owning side to null (unless already changed)
            if ($menuCategory->getSubCategory() === $this) {
                $menuCategory->setSubCategory(null);
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
            $remiseGroupe->setSubCategories($this);
        }

        return $this;
    }

    public function removeRemiseGroupe(RemiseGroupe $remiseGroupe): self
    {
        if ($this->remiseGroupes->removeElement($remiseGroupe)) {
            // set the owning side to null (unless already changed)
            if ($remiseGroupe->getSubCategories() === $this) {
                $remiseGroupe->setSubCategories(null);
            }
        }

        return $this;
    }
}
