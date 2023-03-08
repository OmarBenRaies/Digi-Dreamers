<?php

namespace App\Entity;

use App\Repository\PublicationRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: PublicationRepository::class)]
class Publication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min:12,minMessage: "Too short!")]
    #[Assert\Length(max:140,maxMessage: "Too long!")]
    private ?string $ContenuPub = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]    
    private ?\DateTimeInterface $DatePub = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min:4,minMessage: "Title is too short!")]
    #[Assert\Length(max:40,maxMessage: "Title is too long!")]

    private ?string $CodePub = null;

    #[ORM\Column(length: 255)]
    private ?string $UrlImagePub = null;

    #[ORM\OneToMany(mappedBy: 'Publication', targetEntity: Commentaire::class, orphanRemoval: true)]
    private Collection $commentaires;

    #[ORM\Column]
    private ?bool $all_day = null;

    #[ORM\Column(length: 7)]
    private ?string $background_color = null;

    #[ORM\Column(length: 7)]
    private ?string $border_color = null;

    #[ORM\Column(length: 7)]
    private ?string $text_color = null;

    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: PubLike::class, orphanRemoval: true)]
    private Collection $Likes;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        $this->DatePub=new \DateTime();
        $this->Likes = new ArrayCollection();
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

   

    public function __toString() {
        return $this->CodePub;
    
}

    public function isAllDay(): ?bool
    {
        return $this->all_day;
    }
    public function getAllDay(): ?bool
    {
        return $this->all_day;
    }

   
    public function setAllDay(bool $all_day): self
    {
        $this->all_day = $all_day;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->background_color;
    }

    public function setBackgroundColor(string $background_color): self
    {
        $this->background_color = $background_color;

        return $this;
    }

    public function getBorderColor(): ?string
    {
        return $this->border_color;
    }

    public function setBorderColor(string $border_color): self
    {
        $this->border_color = $border_color;

        return $this;
    }

    public function getTextColor(): ?string
    {
        return $this->text_color;
    }

    public function setTextColor(string $text_color): self
    {
        $this->text_color = $text_color;

        return $this;
    }

    /**
     * @return Collection<int, PubLike>
     */
    public function getLikes(): Collection
    {
        return $this->Likes;
    }

    public function addLike(PubLike $like): self
    {
        if (!$this->Likes->contains($like)) {
            $this->Likes->add($like);
            $like->setPublication($this);
        }

        return $this;
    }

    public function removeLike(PubLike $like): self
    {
        if ($this->Likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getPublication() === $this) {
                $like->setPublication(null);
            }
        }

        return $this;
    }

    public function isLikedByUser(User $user): bool
    {
        foreach ($this->Likes as $like){
            if ($like->getUser() === $user) return true;
        }
       return false;
    }
}