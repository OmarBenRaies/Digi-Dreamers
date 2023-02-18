<?php

namespace App\Entity;

use App\Repository\CategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoriesRepository::class)]
class Categories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Code_cat = null;

    #[ORM\Column(length: 255)]
    private ?string $Nom_cat = null;

    #[ORM\OneToMany(mappedBy: 'Categorie', targetEntity: Produits::class)]
    private Collection $produits;

   
    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeCat(): ?string
    {
        return $this->Code_cat;
    }

    public function setCodeCat(string $Code_cat): self
    {
        $this->Code_cat = $Code_cat;

        return $this;
    }

    public function getNomCat(): ?string
    {
        return $this->Nom_cat;
    }

    public function setNomCat(string $Nom_cat): self
    {
        $this->Nom_cat = $Nom_cat;

        return $this;
    }

    /**
     * @return Collection<int, Produits>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produits $produit): self
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setCategorie($this);
        }

        return $this;
    }

    public function removeProduit(Produits $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getCategorie() === $this) {
                $produit->setCategorie(null);
            }
        }

        return $this;
    }

    

   


}
