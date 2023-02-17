<?php

namespace App\Entity;

use App\Repository\PublicationRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicationRepository::class)]
class Publication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ContenuPub = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $DatePub = null;

    #[ORM\Column(length: 255)]
    private ?string $CodePub = null;

    #[ORM\Column(length: 255)]
    private ?string $UrlImagePub = null;

    #[ORM\OneToMany(mappedBy: 'Publication', targetEntity: Commentaire::class, orphanRemoval: true)]
    private Collection $commentaires;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        $this->DatePub=new \DateTime();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenuPub(): ?string
    {
        return $this->ContenuPub;
    }

    public function setContenuPub(string $ContenuPub): self
    {
        $this->ContenuPub = $ContenuPub;

        return $this;
    }

    public function getDatePub(): ?\DateTimeInterface
    {
        return $this->DatePub;
    }

    public function setDatePub(\DateTimeInterface $DatePub): self
    {
        $this->DatePub = $DatePub;

        return $this;
    }

    public function getCodePub(): ?string
    {
        return $this->CodePub;
    }

    public function setCodePub(string $CodePub): self
    {
        $this->CodePub = $CodePub;

        return $this;
    }

    public function getUrlImagePub(): ?string
    {
        return $this->UrlImagePub;
    }

    public function setUrlImagePub(string $UrlImagePub): self
    {
        $this->UrlImagePub = $UrlImagePub;

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setPublication($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getPublication() === $this) {
                $commentaire->setPublication(null);
            }
        }

        return $this;
    }

    
}
