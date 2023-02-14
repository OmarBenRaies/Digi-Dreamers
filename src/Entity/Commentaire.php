<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ContenuComm = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $DateCom = null;

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
}
