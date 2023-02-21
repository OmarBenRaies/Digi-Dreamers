<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

   // #[Assert\GreaterThanOrEqual("today",message: "Le date n'est pas valide")]
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le lieu est Obligatoire")]
    private ?string $lieu = null;

    #[Assert\Positive]
    #[ORM\Column]
    #[Assert\NotBlank(message: "Le nombre de place est Obligatoire")]
    private ?int $nbr_participant = null;

    #[Assert\Length(min: 3,minMessage: "Le titre doit etre composé au minimum de 3 carateres")]
    #[ORM\Column(length: 255)]
    private ?string $titre = null;
    #[Assert\Length(min: 20,minMessage: "La description doit etre composé au minimum de 20 carateres")]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?float $total = null;

    #[Assert\Positive(message: "Le prix doit etre positif")]
    #[ORM\Column]
    #[Assert\NotBlank(message: "Le prix est Obligatoire")]
    private ?float $prix = null;

    #[ORM\Column(length: 255)]
    private ?string $url_image = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'evenements')]
    private Collection $users;

    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: Don::class)]
    private Collection $DonEvent;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->DonEvent = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getNbrParticipant(): ?int
    {
        return $this->nbr_participant;
    }

    public function setNbrParticipant(int $nbr_participant): self
    {
        $this->nbr_participant = $nbr_participant;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getUrlImage(): ?string
    {
        return $this->url_image;
    }

    public function setUrlImage(string $url_image): self
    {
        $this->url_image = $url_image;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    /**
     * @return Collection<int, Don>
     */
    public function getDonEvent(): Collection
    {
        return $this->DonEvent;
    }

    public function addDonEvent(Don $donEvent): self
    {
        if (!$this->DonEvent->contains($donEvent)) {
            $this->DonEvent->add($donEvent);
            $donEvent->setEvenement($this);
        }

        return $this;
    }

    public function removeDonEvent(Don $donEvent): self
    {
        if ($this->DonEvent->removeElement($donEvent)) {
            // set the owning side to null (unless already changed)
            if ($donEvent->getEvenement() === $this) {
                $donEvent->setEvenement(null);
            }
        }

        return $this;
    }
}
