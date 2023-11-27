<?php

namespace App\Entity;

use App\Repository\CodePromoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CodePromoRepository::class)]
class CodePromo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 16, unique: true)]
    private ?string $code = null;

    #[ORM\Column]
    private ?float $pourcentage = null;

    #[ORM\Column]
    private ?int $utilisation = 0; //valeur de base 0 

    #[ORM\Column(nullable : true, type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $temps = null;

    #[ORM\OneToMany(mappedBy: 'codePromo', targetEntity: HistoriqueCodePromo::class)]
    private Collection $historiqueCodePromo;

    // #[ORM\ManyToOne(inversedBy: 'codePromos')]
    // private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?float $maxAmount = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $produits = [];

    #[ORM\Column(type: Types::ARRAY)]
    private array $users = [];

    #[ORM\Column(nullable: true)]
    private ?int $utilisationMax = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbUtilisationMaxUser = 1;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $subCategories = [];

    #[ORM\Column(nullable: true)]
    private ?int $montantRemise = null;

    public function __construct()
    {
        $this->historiqueCodePromo = new ArrayCollection();
    }

    public function __toString(){

        return $this->getCode();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getPourcentage(): ?float
    {
        return $this->pourcentage;
    }

    public function setPourcentage(?float $pourcentage): self
    {
        $this->pourcentage = $pourcentage;

        return $this;
    }

    public function getUtilisation(): ?int
    {
        return $this->utilisation;
    }

    public function setUtilisation(int $utilisation): self
    {
        $this->utilisation = $utilisation;

        return $this;
    }

    public function getTemps(): ?\DateTimeInterface
    {
        return $this->temps;
    }

    public function setTemps(?\DateTimeInterface $temps): self
    {
        $this->temps = $temps;

        return $this;
    }

    /**
     * @return Collection<int, HistoriqueCodePromo>
     */
    public function getHistoriqueCodePromo(): Collection
    {
        return $this->historiqueCodePromo;
    }

    public function addHistoriqueCodePromo(HistoriqueCodePromo $historiqueCodePromo): self
    {
        if (!$this->historiqueCodePromo->contains($historiqueCodePromo)) {
            $this->historiqueCodePromo->add($historiqueCodePromo);
            $historiqueCodePromo->setCodePromo($this);
        }

        return $this;
    }

    public function removeHistoriqueCodePromo(HistoriqueCodePromo $historiqueCodePromo): self
    {
        if ($this->historiqueCodePromo->removeElement($historiqueCodePromo)) {
            // set the owning side to null (unless already changed)
            if ($historiqueCodePromo->getCodePromo() === $this) {
                $historiqueCodePromo->setCodePromo(null);
            }
        }

        return $this;
    }

    // public function getUser(): ?User
    // {
    //     return $this->user;
    // }

    // public function setUser(?User $user): self
    // {
    //     $this->user = $user;

    //     return $this;
    // }

    public function getMaxAmount(): ?float
    {
        return $this->maxAmount;
    }

    public function setMaxAmount(?float $maxAmount): self
    {
        $this->maxAmount = $maxAmount;

        return $this;
    }

    public function getProduits(): array
    {
        return $this->produits;
    }

    public function setProduits(?array $produits): self
    {
        $this->produits = $produits;

        return $this;
    }

    public function getUsers(): array
    {
        return $this->users;
    }

    public function setUsers(?array $users): self
    {
        $this->users = $users;

        return $this;
    }


    public function getUtilisationMax(): ?int
    {
        return $this->utilisationMax;
    }

    public function setUtilisationMax(?int $utilisationMax): self
    {
        $this->utilisationMax = $utilisationMax;

        return $this;
    }

    public function getNbUtilisationMaxUser(): ?int
    {
        return $this->nbUtilisationMaxUser;
    }

    public function setNbUtilisationMaxUser(?int $nbUtilisationMaxUser): self
    {
        $this->nbUtilisationMaxUser = $nbUtilisationMaxUser;

        return $this;
    }

    public function getSubCategories(): array
    {
        return $this->subCategories;
    }

    public function setSubCategories(?array $subCategories): self
    {
        $this->subCategories = $subCategories;

        return $this;
    }

    public function getMontantRemise(): ?int
    {
        return $this->montantRemise;
    }

    public function setMontantRemise(?int $montantRemise): self
    {
        $this->montantRemise = $montantRemise;

        return $this;
    }
}
