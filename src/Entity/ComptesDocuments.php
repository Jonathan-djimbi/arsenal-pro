<?php

namespace App\Entity;

use App\Repository\ComptesDocumentsRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComptesDocumentsRepository::class)]
class ComptesDocuments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'comptedocuments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $cartId = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?DateTime $cartIdDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $licenceTirId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cartPoliceId = null;

    
    #[ORM\Column(length: 255, nullable: true)]
    private ?DateTime $licenceTirIdDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $certificatMedicalId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?DateTime $certificatMedicalIdDate = null;

    //check
    #[ORM\Column(nullable: false)]
    private ?bool $cartIdcheck = null;
    #[ORM\Column(nullable: false)]
    private ?bool $licenceTirIdcheck = null;
    #[ORM\Column(nullable: false)]
    private ?bool $certificatMedicalIdcheck = null;
    #[ORM\Column(nullable: true)]
    private ?bool $cartPoliceIdcheck = null;

    #[ORM\Column(nullable: false)]
    private ?bool $numero_sea_check = null;

    #[ORM\Column(nullable: false)]
    private ?bool $vosdocumentsverifies = null;

    #[ORM\Column(length: 255)]
    private ?string $numero_sea = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $justificatifDomicile = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEnvoi = null;


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

    public function getCartId(): ?string
    {
        return $this->cartId;
    }

    public function setCartId(?string $cartId): self
    {
        $this->cartId = $cartId;

        return $this;
    }

    public function getCartIddate(): ?DateTime
    {
        return $this->cartIdDate;
    }

    public function setCartIddate(?DateTime $cartIdDate): self
    {
        $this->cartIdDate = $cartIdDate;

        return $this;
    }

    public function getLicenceTirId(): ?string
    {
        return $this->licenceTirId;
    }

    public function setLicenceTirId(?string $licenceTirId): self
    {
        $this->licenceTirId = $licenceTirId;

        return $this;
    }
    public function getLicenceTirIddate(): ?DateTime
    {
        return $this->licenceTirIdDate;
    }

    public function setLicenceTirIddate(?DateTime $licenceTirIdDate): self
    {
        $this->licenceTirIdDate = $licenceTirIdDate;

        return $this;
    }

    public function getCertificatMedicalId(): ?string
    {
        return $this->certificatMedicalId;
    }

    public function setCertificatMedicalId(?string $certificatMedicalId): self
    {
        $this->certificatMedicalId = $certificatMedicalId;

        return $this;
    }
    public function getCertificatMedicalIdDate(): ?DateTime
    {
        return $this->certificatMedicalIdDate;
    }

    public function setCertificatMedicalIdDate(?DateTime $certificatMedicalIdDate): self
    {
        $this->certificatMedicalIdDate = $certificatMedicalIdDate;

        return $this;
    }
    public function getCartIdcheck(): ?bool
    {
        return $this->cartIdcheck;
    }

    public function setCartIdcheck(bool $cartIdcheck): self
    {
        $this->cartIdcheck = $cartIdcheck;

        return $this;
    }
    public function getLicenceTirIdcheck(): ?bool
    {
        return $this->licenceTirIdcheck;
    }

    public function setLicenceTirIdcheck(bool $licenceTirIdcheck): self
    {
        $this->licenceTirIdcheck = $licenceTirIdcheck;

        return $this;
    }

    public function getCertificatMedicalIdcheck(): ?bool
    {
        return $this->certificatMedicalIdcheck;
    }

    public function setCertificatMedicalIdcheck(bool $certificatMedicalIdcheck): self
    {
        $this->certificatMedicalIdcheck = $certificatMedicalIdcheck;

        return $this;
    }

    public function getVosdocumentsverifies(): ?bool
    {
        return $this->vosdocumentsverifies;
    }

    public function setVosdocumentsverifies(bool $vosdocumentsverifies): self
    {
        $this->vosdocumentsverifies = $vosdocumentsverifies;

        return $this;
    }

    public function getNumeroSea(): ?string
    {
        return $this->numero_sea;
    }

    public function setNumeroSea(string $numero_sea): self
    {
        $this->numero_sea = $numero_sea;

        return $this;
    }

    public function isNumeroSeaCheck(): ?bool
    {
        return $this->numero_sea_check;
    }

    public function setNumeroSeaCheck(bool $numero_sea_check): self
    {
        $this->numero_sea_check = $numero_sea_check;

        return $this;
    }

    public function getJustificatifDomicile(): ?string
    {
        return $this->justificatifDomicile;
    }

    public function setJustificatifDomicile(?string $justificatifDomicile): self
    {
        $this->justificatifDomicile = $justificatifDomicile;

        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->dateEnvoi;
    }

    public function setDateEnvoi(\DateTimeInterface $dateEnvoi): self
    {
        $this->dateEnvoi = $dateEnvoi;

        return $this;
    }

    public function getCartPoliceId(): ?string
    {
        return $this->cartPoliceId;
    }

    public function setCartPoliceId(?string $cartPoliceId): self
    {
        $this->cartPoliceId = $cartPoliceId;

        return $this;
    }

    public function isCartPoliceIdcheck(): ?bool
    {
        return $this->cartPoliceIdcheck;
    }

    public function setCartPoliceIdcheck(?bool $cartPoliceIdcheck): self
    {
        $this->cartPoliceIdcheck = $cartPoliceIdcheck;

        return $this;
    }
}
