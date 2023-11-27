<?php

namespace App\Entity;

use App\Repository\PubRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PubRepository::class)]
class Pub
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(nullable: true)]
    private ?int $section = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $texte = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $texteCouleur = null;

    #[ORM\Column]
    private ?bool $imageAffiche = null;

    // #[ORM\Column(length: 255, nullable: true)]
    // private ?string $intituleStatic = null; //Libellé de la bannière pour quelle page UNUSED


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getSection(): ?int
    {
        return $this->section;
    }

    public function setSection(?int $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(?string $texte): self
    {
        $this->texte = $texte;

        return $this;
    }

    public function getTexteCouleur(): ?string
    {
        return $this->texteCouleur;
    }

    public function setTexteCouleur(?string $texteCouleur): self
    {
        $this->texteCouleur = $texteCouleur;

        return $this;
    }

    public function isImageAffiche(): ?bool
    {
        return $this->imageAffiche;
    }

    public function setImageAffiche(bool $imageAffiche): self
    {
        $this->imageAffiche = $imageAffiche;

        return $this;
    }

    // public function getIntituleStatic(): ?string
    // {
    //     return $this->intituleStatic;
    // }

    // public function setIntituleStatic(? string $intituleStatic): self
    // {
    //     $this->intituleStatic = $intituleStatic;

    //     return $this;
    // }
}
