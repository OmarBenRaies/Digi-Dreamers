<?php

namespace App\Entity;

use App\Repository\PublicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CommentaireRepository;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min:4,minMessage:"too short!")]
    #[Assert\Length(max:60,maxMessage: "Too long!")]

    private ?string $ContenuComm = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $DateCom = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Publication $Publication = null;



    public function __construct()
    {
        $this->DateCom=new \DateTime();
    }
   

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenuComm(): ?string
    {
        return $this->ContenuComm;
    }

    public function setContenuComm(string $ContenuComm): self
    {
        $this->ContenuComm = $ContenuComm;

        return $this;
    }

    public function getDateCom(): ?\DateTimeInterface
    {
        return $this->DateCom;
    }

    public function setDateCom(\DateTimeInterface $DateCom): self
    {
        $this->DateCom = $DateCom;

        return $this;
    }

    public function getPublication(): ?Publication
    {
        return $this->Publication;
    }

    public function setPublication(?Publication $Publication): self
    {
        $this->Publication = $Publication;

        return $this;
    }

    public function __toString() {
        return $this->ContenuComm;
    
}


}
