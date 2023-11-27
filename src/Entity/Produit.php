<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $illustration = null; //image main

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $illustrationun = null; //image deux

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $illustrationdeux = null; //image trois

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $illustrationtrois = null; //image quatre

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $illustrationquatre = null; //image cinq

    #[ORM\Column(length: 100)]
    private ?string $subtitle = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $codeRga = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $caracteristique = "-";    
    
    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(nullable: true)]
    private ?float $pricepromo = null;

    #[ORM\Column(nullable: true)]
    private ?float $priceFDO = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?SubCategory $subCategory = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(name: "marque_id", referencedColumnName: "id", nullable: false)]
    private ?Marque $marque = null;

    #[ORM\Column(nullable: false)]
    private ?int $quantite = 0;

    #[ORM\Column(nullable: true)]
    private ?bool $isAffiche = null;

    #[ORM\Column]
    private ?bool $isBest = false;

    #[ORM\Column(nullable: true)]
    private ?bool $isDegressif = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isOccassion = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isSuisse = null;

    #[ORM\Column(nullable: true)]
    private ?string $accessoireLieA = null;
    
    #[ORM\Column(nullable: true)]
    private ?float $masse = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isVenteFlash = false;
    
    #[ORM\Column(nullable: true)]
    private ?bool $isForcesOrdre = null;
    
    #[ORM\ManyToOne(inversedBy: 'produit')]
    #[ORM\JoinColumn(nullable: true)]
    private ?MainPortee $mainportee = null;

    #[ORM\OneToMany(mappedBy: 'pid', targetEntity: OrderDetails::class)]
    private Collection $orderDetails;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: MailRetourStock::class)]
    private Collection $produitMailStockIn;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Famille $famille = null;

    #[ORM\ManyToOne(inversedBy: 'produit')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Calibre $calibres = null;

    #[ORM\OneToMany(mappedBy: 'pid', targetEntity: VenteFlash::class)]
    private Collection $produitsFlash;

    #[ORM\OneToMany(mappedBy: 'pid', targetEntity: BourseArmes::class)]
    private Collection $bourseArmes;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Fournisseurs $fournisseurs = null;

    #[ORM\Column(nullable: true)]
    private ?int $munitionNbBoite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referenceAssociation = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isCarteCadeau = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?Taille $taille = null;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
        $this->produitMailStockIn = new ArrayCollection();
        $this->produitsFlash = new ArrayCollection();
        $this->bourseArmes = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getIllustration(): ?string
    {
        return $this->illustration;
    }

    public function setIllustration(?string $illustration): self
    {
        $this->illustration = $illustration;

        return $this;
    }

    public function getIllustrationUn(): ?string
    {
        return $this->illustrationun;
    }

    public function setIllustrationUn(?string $illustrationun): self
    {
        $this->illustrationun = $illustrationun;

        return $this;
    }

    public function getIllustrationDeux(): ?string
    {
        return $this->illustrationdeux;
    }

    public function setIllustrationDeux(?string $illustrationdeux): self
    {
        $this->illustrationdeux = $illustrationdeux;

        return $this;
    }

    public function getIllustrationTrois(): ?string
    {
        return $this->illustrationtrois;
    }

    public function setIllustrationTrois(?string $illustrationtrois): self
    {
        $this->illustrationtrois = $illustrationtrois;

        return $this;
    }

    
    public function getIllustrationQuatre(): ?string
    {
        return $this->illustrationquatre;
    }

    public function setIllustrationQuatre(?string $illustrationquatre): self
    {
        $this->illustrationquatre = $illustrationquatre;

        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(string $subtitle): self
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getCodeRga(): ?string
    {
        return $this->codeRga;
    }

    public function setCodeRga(?string $codeRga): self
    {
        $this->codeRga = $codeRga;

        return $this;
    }


    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCaracteristique(): ?string
    {
        return $this->caracteristique;
    }

    public function setCaracteristique(string $caracteristique): self
    {
        $this->caracteristique = $caracteristique;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPricePromo(): ?float
    {
        return $this->pricepromo;
    }

    public function setPricePromo(?float $pricepromo): self
    {
        $this->pricepromo = $pricepromo;

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
    public function getMarque(): ?Marque
    {
        return $this->marque;
    }

    public function setMarque(?Marque $marque): self
    {
        $this->marque = $marque;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getIsAffiche(): ?bool
    {
        return $this->isAffiche;
    }

    public function setIsAffiche(bool $isAffiche): self
    {
        $this->isAffiche = $isAffiche;

        return $this;
    }

    public function isIsBest(): ?bool
    {
        return $this->isBest;
    }

    public function setIsBest(bool $isBest): self
    {
        $this->isBest = $isBest;

        return $this;
    }

    public function getIsDegressif(): ?bool
    {
        return $this->isDegressif;
    }

    public function setIsDegressif(bool $isDegressif): self
    {
        $this->isDegressif = $isDegressif;

        return $this;
    }

    public function getDegressifValues(){ //tableau défini les valeurs par boites des produits MUNITION EN DEGRESSIF
        return [1, 10, 20, 40, 100, 500, 1000];
    }

    public function getIsOccassion(): ?bool
    {
        return $this->isOccassion;
    }

    public function setIsOccassion(bool $isOccassion): self
    {
        $this->isOccassion = $isOccassion;

        return $this;
    }
    public function isSuisse(): ?bool
    {
        return $this->isSuisse;
    }

    public function setIsSuisse(bool $isSuisse): self
    {
        $this->isSuisse = $isSuisse;

        return $this;
    }

    public function getAccessoireLieA(): ?string
    {
        return $this->accessoireLieA;
    }

    public function setAccessoireLieA(?string $accessoireLieA): self
    {
        $this->accessoireLieA = $accessoireLieA;

        return $this;
    }

    public function getMainportee(): ?MainPortee
    {
        return $this->mainportee;
    }

    public function setMainportee(?MainPortee $mainportee): self
    {
        $this->mainportee = $mainportee;

        return $this;
    }
    
    public function getMasse(): ?float
    {
        return $this->masse;
    }

    public function setMasse(?float $masse): self
    {
        $this->masse = $masse;

        return $this;
    }
    /**
     * @return Collection<int, OrderDetails>
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetails $orderDetail): self
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails->add($orderDetail);
            $orderDetail->setPid($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetails $orderDetail): self
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getPid() === $this) {
                $orderDetail->setPid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MailRetourStock>
     */
    public function getProduitMailStockIn(): Collection
    {
        return $this->produitMailStockIn;
    }

    public function addProduitMailStockIn(MailRetourStock $produitMailStockIn): self
    {
        if (!$this->produitMailStockIn->contains($produitMailStockIn)) {
            $this->produitMailStockIn->add($produitMailStockIn);
            $produitMailStockIn->setProduit($this);
        }

        return $this;
    }

    public function removeProduitMailStockIn(MailRetourStock $produitMailStockIn): self
    {
        if ($this->produitMailStockIn->removeElement($produitMailStockIn)) {
            // set the owning side to null (unless already changed)
            if ($produitMailStockIn->getProduit() === $this) {
                $produitMailStockIn->setProduit(null);
            }
        }

        return $this;
    }

    public function isIsForcesOrdre(): ?bool
    {
        return $this->isForcesOrdre;
    }

    public function setIsForcesOrdre(?bool $isForcesOrdre): self
    {
        $this->isForcesOrdre = $isForcesOrdre;

        return $this;
    }

    public function getFamille(): ?Famille
    {
        return $this->famille;
    }

    public function setFamille(?Famille $famille): self
    {
        $this->famille = $famille;

        return $this;
    }

    public function getCalibres(): ?Calibre
    {
        return $this->calibres;
    }

    public function setCalibres(?Calibre $calibres): self
    {
        $this->calibres = $calibres;

        return $this;
    }

    public function isVenteFlash(): ?bool
    {
        return $this->isVenteFlash;
    }

    public function setIsVenteFlash(?bool $isVenteFlash): self
    {
        $this->isVenteFlash = $isVenteFlash;

        return $this;
    }

    /**
     * @return Collection<int, VenteFlash>
     */
    public function getProduitsFlash(): Collection
    {
        return $this->produitsFlash;
    }

    public function addProduitsFlash(VenteFlash $produitsFlash): self
    {
        if (!$this->produitsFlash->contains($produitsFlash)) {
            $this->produitsFlash->add($produitsFlash);
            $produitsFlash->setPid($this);
        }

        return $this;
    }

    public function removeProduitsFlash(VenteFlash $produitsFlash): self
    {
        if ($this->produitsFlash->removeElement($produitsFlash)) {
            // set the owning side to null (unless already changed)
            if ($produitsFlash->getPid() === $this) {
                $produitsFlash->setPid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BourseArmes>
     */
    public function getBourseArmes(): Collection
    {
        return $this->bourseArmes;
    }

    public function addBourseArme(BourseArmes $bourseArme): self
    {
        if (!$this->bourseArmes->contains($bourseArme)) {
            $this->bourseArmes->add($bourseArme);
            $bourseArme->setPid($this);
        }

        return $this;
    }

    public function removeBourseArme(BourseArmes $bourseArme): self
    {
        if ($this->bourseArmes->removeElement($bourseArme)) {
            // set the owning side to null (unless already changed)
            if ($bourseArme->getPid() === $this) {
                $bourseArme->setPid(null);
            }
        }

        return $this;
    }

    public function getFournisseurs(): ?Fournisseurs
    {
        return $this->fournisseurs;
    }

    public function setFournisseurs(?Fournisseurs $fournisseurs): self
    {
        $this->fournisseurs = $fournisseurs;

        return $this;
    }

    public function getSubCategory(): ?SubCategory
    {
        return $this->subCategory;
    }

    public function setSubCategory(?SubCategory $subCategory): self
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    public function getPriceFDO(): ?float
    {
        return $this->priceFDO;
    }

    public function setPriceFDO(?float $priceFDO): self
    {
        $this->priceFDO = $priceFDO;

        return $this;
    }

    public function getMunitionNbBoite(): ?int
    {
        return $this->munitionNbBoite;
    }

    public function setMunitionNbBoite(?int $munitionNbBoite): self
    {
        $this->munitionNbBoite = $munitionNbBoite;

        return $this;
    }

    public function getReferenceAssociation(): ?string
    {
        return $this->referenceAssociation;
    }

    public function setReferenceAssociation(?string $referenceAssociation): self
    {
        $this->referenceAssociation = $referenceAssociation;

        return $this;
    }

    public function isIsCarteCadeau(): ?bool
    {
        return $this->isCarteCadeau;
    }

    public function setIsCarteCadeau(?bool $isCarteCadeau): self
    {
        $this->isCarteCadeau = $isCarteCadeau;

        return $this;
    }

    public function getTaille(): ?Taille
    {
        return $this->taille;
    }

    public function setTaille(?Taille $taille): self
    {
        $this->taille = $taille;

        return $this;
    }
}
