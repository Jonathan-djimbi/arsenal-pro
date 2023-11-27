<?php

namespace App\Entity;

use App\Repository\DepotVenteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepotVenteRepository::class)]
class DepotVente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'contact')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column]
    private ?int $postal = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 12)]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column]
    private ?int $nbArmePoing = null;

    #[ORM\Column]
    private ?int $nbArmeEpaule = null;

    #[ORM\Column]
    private ?int $nbTotalArme = null;

    #[ORM\Column(length: 255)]
    private ?string $prixLot = null;

    #[ORM\Column(length: 2500)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $faitLe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoUn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoDeux = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoTrois = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoQuatre = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isLegit = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(\DateTimeInterface $dateNaissance): self
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getPostal(): ?int
    {
        return $this->postal;
    }

    public function setPostal(int $postal): self
    {
        $this->postal = $postal;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
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

    public function getNbArmePoing(): ?int
    {
        return $this->nbArmePoing;
    }

    public function setNbArmePoing(int $nbArmePoing): self
    {
        $this->nbArmePoing = $nbArmePoing;

        return $this;
    }

    public function getNbArmeEpaule(): ?int
    {
        return $this->nbArmeEpaule;
    }

    public function setNbArmeEpaule(int $nbArmeEpaule): self
    {
        $this->nbArmeEpaule = $nbArmeEpaule;

        return $this;
    }

    public function getNbTotalArme(): ?int
    {
        return $this->nbTotalArme;
    }

    public function setNbTotalArme(int $nbTotalArme): self
    {
        $this->nbTotalArme = $nbTotalArme;

        return $this;
    }

    public function getPrixLot(): ?string
    {
        return $this->prixLot;
    }

    public function setPrixLot(string $prixLot): self
    {
        $this->prixLot = $prixLot;

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

    public function getFaitLe(): ?\DateTimeInterface
    {
        return $this->faitLe;
    }

    public function setFaitLe(\DateTimeInterface $faitLe): self
    {
        $this->faitLe = $faitLe;

        return $this;
    }

    public function getPhotoUn(): ?string
    {
        return $this->photoUn;
    }

    public function setPhotoUn(?string $photoUn): self
    {
        $this->photoUn = $photoUn;

        return $this;
    }

    public function getPhotoDeux(): ?string
    {
        return $this->photoDeux;
    }

    public function setPhotoDeux(?string $photoDeux): self
    {
        $this->photoDeux = $photoDeux;

        return $this;
    }

    public function getPhotoTrois(): ?string
    {
        return $this->photoTrois;
    }

    public function setPhotoTrois(?string $photoTrois): self
    {
        $this->photoTrois = $photoTrois;

        return $this;
    }

    public function getPhotoQuatre(): ?string
    {
        return $this->photoQuatre;
    }

    public function setPhotoQuatre(?string $photoQuatre): self
    {
        $this->photoQuatre = $photoQuatre;

        return $this;
    }

    public function isIsLegit(): ?bool
    {
        return $this->isLegit;
    }

    public function setIsLegit(?bool $isLegit): self
    {
        $this->isLegit = $isLegit;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
