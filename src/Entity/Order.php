<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{

    // private EntityManagerInterface $entityManager;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createAt = null;

    #[ORM\Column(length: 255)]
    private ?string $carrierName = null;

    #[ORM\Column]
    private ?float $carrierPrice = null;

    // #[ORM\Column(type: Types::TEXT)]
    // private ?string $delivry = null;

    #[ORM\OneToMany(mappedBy: 'myOrder', targetEntity: OrderDetails::class)]
    private Collection $orderDetails;


    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\Column(nullable: true,length: 16)]
    private ?string $promo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeSessionId = null;

    #[ORM\Column]
    private ?int $state = null;
    #[ORM\Column(nullable:true)]
    private ?int $pointFideliteUtilise = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Adress $delivry = null;

    #[ORM\Column(nullable: true)]
    private ?float $refundAmount = null;

    #[ORM\Column(nullable: true)]
    private ?int $montantCompteUtilise = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $refundedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $remisePromoEuros = null;

    public function __construct()
    {
        // $this->entityManager = $entityManager;
        $this->orderDetails = new ArrayCollection();

    }

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

    public function getUserDetails(){ //affichage détail utilisateur pour les commandes
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

    public function getCarrierName(): ?string
    {
        return $this->carrierName;
    }

    public function setCarrierName(string $carrierName): self
    {
        $this->carrierName = $carrierName;
        return $this;
    }

    public function getCarrierPrice(): ?float
    {
        return $this->carrierPrice;
    }

    public function setCarrierPrice(float $carrierPrice): self
    {
        $this->carrierPrice = $carrierPrice;
        return $this;
    }

    // public function getDelivry(): ?string
    // {
    //     return $this->delivry;
    // }

    // public function setDelivry(string $delivry): self
    // {
    //     $this->delivry = $delivry;

    //     return $this;
    // }

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
            $orderDetail->setMyOrder($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetails $orderDetail): self
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getMyOrder() === $this) {
                $orderDetail->setMyOrder(null);
            }
        }

        return $this;
    }

   
    public function getTotal()
    { //get total des produits en tout
      $total = null;
      foreach ($this->getOrderDetails()->getValues() as $produit){
            $total = $total + ($produit->getPrice() * $produit->getQuantity());
      }
      return $total;
    }

    public function getTotalFinal(){ // get total des produits FINAL (affiché dans le dashboard et dans le journal des ventes excel)
        $total = null;
        foreach ($this->getOrderDetails()->getValues() as $produit){
            $total = $total + ($produit->getPrice() * $produit->getQuantity());
        }
        if($this->getPointFideliteUtilise() > 0){
            $total = $total - ($this->getPointFideliteUtilise());
        }
        if($this->getMontantCompteUtilise() > 0){
            $total = $total - ($this->getMontantCompteUtilise());
        }
        if($this->getRemisePromoEuros() > 0){
            $total = $total - ($this->getRemisePromoEuros());
        }
        if($this->getCarrierPrice() > 0){
            $total = $total + ($this->getCarrierPrice()); 
        }
        return $total;
    }

    public function getDateAt(){ //reformatage date getCreate car sur le dashboard ça affiché "Aucun"
        return $this->createAt->format('d/m/Y');
    }

    // public function getOldTotalIfPromo(){ // get ancien total des produits FINAL (affiché dans le dashboard et dans le journal des ventes excel)
    //     $total = null;
    //     foreach ($this->getOrderDetails()->getValues() as $produit){
    //              $total = $total + ($produit->getPrice() * $produit->getQuantity());
    //     }
    //     if($this->getPointFideliteUtilise() > 0){
    //         $total = $total - ($this->getPointFideliteUtilise());
    //     }
    //     return $total;
    // }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getPromo(): ?string
    {
        return $this->promo;
    }

    public function setPromo(?string $promo): self
    {
        $this->promo = $promo;

        return $this;
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(?string $stripeSessionId): self
    {
        $this->stripeSessionId = $stripeSessionId;

        return $this;
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
    public function getPointFideliteUtilise(): ?int
    {
        return $this->pointFideliteUtilise;
    }

    public function setPointFideliteUtilise(int $pointFideliteUtilise): self
    {
        $this->pointFideliteUtilise = $pointFideliteUtilise;

        return $this;
    }

    public function getPointFideliteUtiliseFormate()
    {
        return $this->pointFideliteUtilise/10;
    }

    public function getPointFideliteGagne(){
        $point = 0;
        $sommeCompte = 0;
        // $userLevel = $this->getUser()->getCarteFidelite()[0]->getNombreAchat();
        // $point_fidelite = $this->entityManager->getRepository(PointFidelite::class)->findAll(); 
        if(!$this->promo && !$this->getPointFideliteUtilise()){
            // if ($userLevel >= 2 && $userLevel < 6){ //Niveau 1 fidelite
            //     $point = $point + 5;
            // }
            // if ($userLevel  >= 6 && $userLevel < 12){ //Niveau 2 fidelite
            //     $point = $point + 10;
            // } 
            // if ($userLevel  >= 12){ //Niveau 3 fidelite
            //     $point = $point + 15;
            // }

            $total = null;
            $total_degressif_deduire = 0;
            foreach ($this->getOrderDetails()->getValues() as $produit){
                $total = $total + ($produit->getPrice() * $produit->getQuantity());
                if($produit->getPid()){
                    if($produit->getPid()->getIsDegressif()){
                        $total_degressif_deduire = $total_degressif_deduire + ($produit->getPrice() * $produit->getQuantity());
                    }
                }
            }
            // if($this->getPointFideliteUtilise() > 0){ 
            //     $total = $total - ($this->getPointFideliteUtilise());
            // }
            if($this->getMontantCompteUtilise() > 0){ //Montant compte utilisé
                $sommeCompte = ($this->getMontantCompteUtilise());
            }
            //Si taux fidélité mis à jour, veuillez mettre ces données à jour, car je n'ai pas réussi à fetch les données depuis une classe d'entité
            $montant = number_format(($this->getTotalFinal() - $total_degressif_deduire + ($sommeCompte)) / 2000);  //2000 = à partir de 20€ fidélité.
            $point = $point + ($montant * 10); //10 incrément de point
        }
        return $point;
    }

    public function getDelivry(): ?Adress
    {   
        return $this->delivry;
    }

    public function setDelivry(?Adress $delivry): self
    {
        $this->delivry = $delivry;

        return $this;
    }

    public function getRefundAmount(): ?float
    {
        return $this->refundAmount;
    }

    public function setRefundAmount(?float $refundAmount): self
    {
        $this->refundAmount = $refundAmount;

        return $this;
    }

    public function getMontantCompteUtilise(): ?int
    {
        return $this->montantCompteUtilise;
    }

    public function setMontantCompteUtilise(?int $montantCompteUtilise): self
    {
        $this->montantCompteUtilise = $montantCompteUtilise;

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

    public function getRemisePromoEuros(): ?int
    {
        return $this->remisePromoEuros;
    }

    public function setRemisePromoEuros(?int $remisePromoEuros): self
    {
        $this->remisePromoEuros = $remisePromoEuros;

        return $this;
    }

}
