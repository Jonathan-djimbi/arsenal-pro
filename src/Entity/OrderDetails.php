<?php

namespace App\Entity;

use App\Repository\OrderDetailsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderDetailsRepository::class)]
class OrderDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderDetails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $myOrder = null;

    #[ORM\Column(length: 255)]
    private ?string $product = null;


    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column]
    private ?float $total = null;

    #[ORM\ManyToOne(inversedBy: 'orderDetails')]
    private ?Produit $pid = null;

    #[ORM\Column(nullable: true)]
    private ?float $totalOriginIfPromo = null;

    public function __toString()
    {
        $pid = "####";
        if($this->getPid()){ //si PID existant dans la BDD
            $pid =  $this->getPid()->getId();
        }
        if($this->getTotalOriginIfPromo() && $this->getTotalOriginIfPromo() > 0){ //si getTotalOriginIfPromo n'est pas vide alors c'est promo
            $string = $this->getProduct().' x'.$this->getQuantity() . ' à ' . number_format(($this->getTotalOriginIfPromo()/100), 2) .'€ (total sans promo) ' . number_format(($this->getPrice()/100) * $this->getQuantity(), 2) .'€ (total avec promo) | ID : ' . $pid;
        } else {
            $string = $this->getProduct().' x'.$this->getQuantity() . ' à ' . number_format(($this->getPrice()/100) * $this->getQuantity(), 2) .'€ (total) | ID : ' . $pid;
        }
        return $string;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMyOrder(): ?Order
    {
        return $this->myOrder;

    }

    public function setMyOrder(?Order $myOrder): self
    {
        $this->myOrder = $myOrder;

        return $this;
    }

    public function getProduct(): ?string
    {
        return $this->product;
    }

    public function setProduct(string $product): self
    {
        $this->product = $product;

        return $this;
    }


    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

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

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getPid(): ?Produit
    {
        return $this->pid;
    }

    public function setPid(?Produit $pid): self
    {
        $this->pid = $pid;

        return $this;
    }

    public function getTotalOriginIfPromo(): ?float
    {
        return $this->totalOriginIfPromo;
    }

    public function setTotalOriginIfPromo(?float $totalOriginIfPromo): self
    {
        $this->totalOriginIfPromo = $totalOriginIfPromo;

        return $this;
    }
}
