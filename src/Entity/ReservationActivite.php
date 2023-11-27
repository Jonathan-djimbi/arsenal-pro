<?php

namespace App\Entity;

use App\Repository\ReservationActiviteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationActiviteRepository::class)]
class ReservationActivite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $descriptionUn = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $descriptionDeux = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $descriptionTrois = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $descriptionQuatre = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $publieLe = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(nullable: false)]
    private ?bool $isOccupe = false;
    
    #[ORM\Column]
    private ?int $type = null;

    #[ORM\OneToMany(mappedBy: 'activite', targetEntity: HistoriqueReservation::class)]
    private Collection $historiqueReservations;

    public function __construct()
    {
        $this->historiqueReservations = new ArrayCollection();
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDescriptionUn(): ?string
    {
        return $this->descriptionUn;
    }

    public function setDescriptionUn(?string $descriptionUn): self
    {
        $this->descriptionUn = $descriptionUn;

        return $this;
    }

    public function getDescriptionDeux(): ?string
    {
        return $this->descriptionDeux;
    }

    public function setDescriptionDeux(?string $descriptionDeux): self
    {
        $this->descriptionDeux = $descriptionDeux;

        return $this;
    }

    public function getDescriptionTrois(): ?string
    {
        return $this->descriptionTrois;
    }

    public function setDescriptionTrois(?string $descriptionTrois): self
    {
        $this->descriptionTrois = $descriptionTrois;

        return $this;
    }

    public function getDescriptionQuatre(): ?string
    {
        return $this->descriptionQuatre;
    }

    public function setDescriptionQuatre(?string $descriptionQuatre): self
    {
        $this->descriptionQuatre = $descriptionQuatre;

        return $this;
    }

    public function getPublieLe(): ?\DateTimeInterface
    {
        return $this->publieLe;
    }

    public function setPublieLe(\DateTimeInterface $publieLe): self
    {
        $this->publieLe = $publieLe;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

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

    public function isOccupe(): ?bool
    {
        return $this->isOccupe;
    }

    public function setIsOccupe(bool $isOccupe): self
    {
        $this->isOccupe = $isOccupe;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Collection<int, HistoriqueReservation>
     */
    public function getHistoriqueReservations(): Collection
    {
        return $this->historiqueReservations;
    }

    public function addHistoriqueReservation(HistoriqueReservation $historiqueReservation): self
    {
        if (!$this->historiqueReservations->contains($historiqueReservation)) {
            $this->historiqueReservations->add($historiqueReservation);
            $historiqueReservation->setActivite($this);
        }

        return $this;
    }

    public function removeHistoriqueReservation(HistoriqueReservation $historiqueReservation): self
    {
        if ($this->historiqueReservations->removeElement($historiqueReservation)) {
            // set the owning side to null (unless already changed)
            if ($historiqueReservation->getActivite() === $this) {
                $historiqueReservation->setActivite(null);
            }
        }

        return $this;
    }
}
