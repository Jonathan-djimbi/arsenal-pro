<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Order::class)]
    private Collection $orders;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Adress::class)]
    private Collection $adresses;

    
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ComptesDocuments::class)]
    private Collection $comptedocuments;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Contact::class)]
    private Collection $contact;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: HistoriqueCodePromo::class)]
    private Collection $codepromo;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CarteFidelite::class)]
    private Collection $carteFidelite;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: HistoriqueReservation::class)]
    private Collection $historiqueReservations;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ProfessionnelAssociationCompte::class)]
    private Collection $professionnelAssociationComptes;

    // #[ORM\OneToMany(mappedBy: 'user', targetEntity: CodePromo::class)]
    // private Collection $codePromos;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CartDatabase::class)]
    private Collection $cartDatabases;

    #[ORM\OneToMany(mappedBy: 'claimedBy', targetEntity: CarteCadeau::class)]
    private Collection $carteCadeaux;
   

    public function __construct()
    {
        $this->adresses = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->comptedocuments = new ArrayCollection();
        $this->contact = new ArrayCollection();
        $this->codepromo = new ArrayCollection();
        $this->carteFidelite = new ArrayCollection();
        $this->historiqueReservations = new ArrayCollection();
        $this->professionnelAssociationComptes = new ArrayCollection();
        // $this->codePromos = new ArrayCollection();
        $this->cartDatabases = new ArrayCollection();
        $this->carteCadeaux = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFullName(): string
    {
          return  $this->getFirstname() .' '.$this->getLastname();
    }


    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function __toString()
    {
        return $this->getId() . " | " . $this->getFullName();
    }
    

    /**
     * @return Collection<int, Adress>
     */
    public function getAdresses(): Collection
    {
        return $this->adresses;
    }

    public function addAdress(Adress $adress): self
    {
        if (!$this->adresses->contains($adress)) {
            $this->adresses->add($adress);
            $adress->setUser($this);
        }

        return $this;
    }

    public function removeAdress(Adress $adress): self
    {
        if ($this->adresses->removeElement($adress)) {
            // set the owning side to null (unless already changed)
            if ($adress->getUser() === $this) {
                $adress->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }
    
     /**
     * @return Collection<int, Order>
     */
    public function getDocuments(): Collection
    {
        return $this->comptedocuments;
    }

    public function addDocuments(ComptesDocuments $comptedocuments): self
    {
        if (!$this->comptedocuments->contains($comptedocuments)) {
            $this->comptedocuments->add($comptedocuments);
            $comptedocuments->setUser($this);
        }

        return $this;
    }

    public function removeDocuments(ComptesDocuments $comptedocuments): self
    {
        if ($this->comptedocuments->removeElement($comptedocuments)) {
            // set the owning side to null (unless already changed)
            if ($comptedocuments->getUser() === $this) {
                $comptedocuments->setUser(null);
            }
        }

        return $this;
    }
    // relation pour les messages par contact
     /**
     * @return Collection<int, Order>
     */
    public function getContact(): Collection
    {
        return $this->contact;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contact->contains($contact)) {
            $this->contact->add($contact);
            $contact->setUser($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contact->removeElement($contact)) {
            // set the owning side to null (unless already changed)
            if ($contact->getUser() === $this) {
                $contact->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HistoriqueCodePromo>
     */
    public function getCodepromo(): Collection
    {
        return $this->codepromo;
    }

    public function addCodepromo(HistoriqueCodePromo $codepromo): self
    {
        if (!$this->codepromo->contains($codepromo)) {
            $this->codepromo->add($codepromo);
            $codepromo->setClient($this);
        }

        return $this;
    }

    public function removeCodepromo(HistoriqueCodePromo $codepromo): self
    {
        if ($this->codepromo->removeElement($codepromo)) {
            // set the owning side to null (unless already changed)
            if ($codepromo->getClient() === $this) {
                $codepromo->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CarteFidelite>
     */
    public function getCarteFidelite(): Collection
    {
        return $this->carteFidelite;
    }
    public function addCarteFidelite(CarteFidelite $carteFidelite): self
    {
        if (!$this->carteFidelite->contains($carteFidelite)) {
            $this->carteFidelite->add($carteFidelite);
            $carteFidelite->setUser($this);
        }

        return $this;
    }

    public function removeCarteFidelite(CarteFidelite $carteFidelite): self
    {
        if ($this->carteFidelite->removeElement($carteFidelite)) {
            // set the owning side to null (unless already changed)
            if ($carteFidelite->getUser() === $this) {
                $carteFidelite->setUser(null);
            }
        }

        return $this;
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
            $historiqueReservation->setUser($this);
        }

        return $this;
    }

    public function removeHistoriqueReservation(HistoriqueReservation $historiqueReservation): self
    {
        if ($this->historiqueReservations->removeElement($historiqueReservation)) {
            // set the owning side to null (unless already changed)
            if ($historiqueReservation->getUser() === $this) {
                $historiqueReservation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProfessionnelAssociationCompte>
     */
    public function getProfessionnelAssociationComptes(): Collection
    {
        return $this->professionnelAssociationComptes;
    }

    public function addProfessionnelAssociationCompte(ProfessionnelAssociationCompte $professionnelAssociationCompte): self
    {
        if (!$this->professionnelAssociationComptes->contains($professionnelAssociationCompte)) {
            $this->professionnelAssociationComptes->add($professionnelAssociationCompte);
            $professionnelAssociationCompte->setUser($this);
        }

        return $this;
    }

    public function removeProfessionnelAssociationCompte(ProfessionnelAssociationCompte $professionnelAssociationCompte): self
    {
        if ($this->professionnelAssociationComptes->removeElement($professionnelAssociationCompte)) {
            // set the owning side to null (unless already changed)
            if ($professionnelAssociationCompte->getUser() === $this) {
                $professionnelAssociationCompte->setUser(null);
            }
        }

        return $this;
    }

    // /**
    //  * @return Collection<int, CodePromo>
    //  */
    // public function getCodePromos(): Collection
    // {
    //     return $this->codePromos;
    // }

    /**
     * @return Collection<int, CartDatabase>
     */
    public function getCartDatabases(): Collection
    {
        return $this->cartDatabases;
    }

    public function addCartDatabase(CartDatabase $cartDatabase): self
    {
        if (!$this->cartDatabases->contains($cartDatabase)) {
            $this->cartDatabases->add($cartDatabase);
            $cartDatabase->setUser($this);
        }

        return $this;
    }

    public function removeCartDatabase(CartDatabase $cartDatabase): self
    {
        if ($this->cartDatabases->removeElement($cartDatabase)) {
            // set the owning side to null (unless already changed)
            if ($cartDatabase->getUser() === $this) {
                $cartDatabase->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CarteCadeau>
     */
    public function getCarteCadeaux(): Collection
    {
        return $this->carteCadeaux;
    }

    public function addCarteCadeau(CarteCadeau $carteCadeau): self
    {
        if (!$this->carteCadeaux->contains($carteCadeau)) {
            $this->carteCadeaux->add($carteCadeau);
            $carteCadeau->setClaimedBy($this);
        }

        return $this;
    }

    public function removeCarteCadeau(CarteCadeau $carteCadeau): self
    {
        if ($this->carteCadeaux->removeElement($carteCadeau)) {
            // set the owning side to null (unless already changed)
            if ($carteCadeau->getClaimedBy() === $this) {
                $carteCadeau->setClaimedBy(null);
            }
        }

        return $this;
    }

}