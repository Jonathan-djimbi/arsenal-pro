<?php

namespace App\Entity;

use App\Repository\HistoriqueReservationRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueReservationRepository::class)]
class HistoriqueReservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $reservationPourLe = null;

    #[ORM\ManyToOne(inversedBy: 'historiqueReservations')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'historiqueReservations')]
    private ?ReservationActivite $activite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $activiteName = null;

    #[ORM\Column]
    private ?float $total = null;

    #[ORM\Column(nullable: true)]
    private ?int $typeFormation = null;

    #[ORM\Column]
    private ?int $state = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $refundedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $refundAmountReservation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReservationPourLe(): ?\DateTimeInterface
    {
        return $this->reservationPourLe;
    }

    public function setReservationPourLe(\DateTimeInterface $reservationPourLe): self
    {
        $this->reservationPourLe = $reservationPourLe;

        return $this;
    }

    public function getTypeFormation(): ?int
    {
        return $this->typeFormation;
    }

    public function setTypeFormation(?int $typeFormation): self
    {
        $this->typeFormation = $typeFormation;

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

    public function getUserDetails(){
        return $this->user->getId() . ' | ' .$this->user->getFullName();
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): self
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getDateAt(){ //reformatage date
        return $this->createAt->format('d/m/Y');
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getActivite(): ?ReservationActivite
    {
        return $this->activite;
    }

    public function setActivite(?ReservationActivite $activite): self
    {
        $this->activite = $activite;

        return $this;
    }

    public function getActiviteName(): ?string
    {
        return $this->activiteName;
    }

    public function setActiviteName(string $activiteName): self
    {
        $this->activiteName = $activiteName;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getRefundedAt(): ?\DateTimeImmutable
    {
        return $this->refundedAt;
    }

    public function setRefundedAt(?\DateTimeImmutable $refundedAt): self
    {
        $this->refundedAt = $refundedAt;

        return $this;
    }

    public function getRefundAmountReservation(): ?int
    {
        return $this->refundAmountReservation;
    }

    public function setRefundAmountReservation(?float $refundAmountReservation): self
    {
        $this->refundAmountReservation = $refundAmountReservation;

        return $this;
    }
}
