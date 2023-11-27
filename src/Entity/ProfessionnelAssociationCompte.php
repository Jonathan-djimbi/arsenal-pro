<?php

namespace App\Entity;

use App\Repository\ProfessionnelAssociationCompteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfessionnelAssociationCompteRepository::class)]
class ProfessionnelAssociationCompte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $raisonSocial = null;

    #[ORM\Column(nullable: true)]
    private ?string $siret = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $noTVA = null;

    #[ORM\ManyToOne(inversedBy: 'professionnelAssociationComptes')]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeFDO = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroMatricule = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRaisonSocial(): ?string
    {
        return $this->raisonSocial;
    }

    public function setRaisonSocial(?string $raisonSocial): self
    {
        $this->raisonSocial = $raisonSocial;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): self
    {
        $this->siret = $siret;

        return $this;
    }

    public function getNoTVA(): ?string
    {
        return $this->noTVA;
    }

    public function setNoTVA(?string $noTVA): self
    {
        $this->noTVA = $noTVA;

        return $this;
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

    public function getDesc()
    {
        return "Raison social : " . $this->getRaisonSocial() . " | SIRET : " . $this->getSiret() . " | NoTVA : " . $this->getNoTVA();
    }
    public function getIntituleFDO(){
        $intituleFDO = "";
        $tab = ["Armée", "Administration penitentiaire", "Convoyeurs de fonds", "Douanes", "Gendarmerie", "Police", "Police ferroviaire", "Police municipale", "Police nationale"];
        for($i = 0; count($tab) > $i; $i++){
            if($this->getTypeFDO() == $i){
                $intituleFDO = $tab[$i];
            }
        }
        if($this->getTypeFDO() > count($tab)){
            $intituleFDO = "Inconnu";
        }
        return $intituleFDO;
    }
    
    public function getFDO()
    {
        $intituleFDO = $this->getIntituleFDO();
        return "Type FDO : " . $intituleFDO . " | Matricule : " . $this->getNumeroMatricule();
    }

    public function __toString()
    {
        return "Raison social : " . $this->getRaisonSocial() . " | SIRET : " . $this->getSiret() . " | NoTVA : " . $this->getNoTVA();
    }

    public function getTypeFDO(): ?string
    {
        return $this->typeFDO;
    }

    public function setTypeFDO(?string $typeFDO): self
    {
        $this->typeFDO = $typeFDO;

        return $this;
    }

    public function getNumeroMatricule(): ?string
    {
        return $this->numeroMatricule;
    }

    public function setNumeroMatricule(?string $numeroMatricule): self
    {
        $this->numeroMatricule = $numeroMatricule;

        return $this;
    }

}
