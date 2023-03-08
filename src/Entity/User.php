<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @method string getUserIdentifier()
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[Vich\Uploadable]
class User implements UserInterface,PasswordAuthenticatedUserInterface, \Serializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("users")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups("users")]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups("users")]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email(message: "votre email n'est pas valide.")]
    #[Groups("users")]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups("users")]
    private ?string $password = null;

    #[Assert\EqualTo(
        propertyPath:"password",
        message: "The password and confirmation password do not match",
    )]
    private ?string $confirmPassword = null;

    #[ORM\Column(length: 255)]
    #[Groups("users")]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        min: 8,
        max: 8,
        minMessage: 'le cin doit etre composé de 8 carateres ',
        maxMessage: 'le cin doit etre composé de 8 carateres ',
    )]
    #[Groups("users")]
    private ?string $cin = null;



    #[ORM\Column(type:"json")]
    #[Groups("users")]
    private $roles = [];

    #[ORM\Column]
    #[Groups("users")]
    private ?int $verified = null;

    #[ORM\Column]
    private ?string $verificationCode = null;

    #[ORM\Column(nullable: true, options: ["default" => ""])]
    #[Groups("users")]
    private ?string $image;

    #[Vich\UploadableField(mapping:"user_images", fileNameProperty:"image")]
    private File $imageFile;

    #[ORM\Column(type:"datetime",options: ["default" => "CURRENT_TIMESTAMP"])]
    private \DateTime $updatedAt;


    public function __construct()
    {
        $this->updatedAt = new \DateTime();
        $this->id = 0;
        $this->nom = '';
        $this->prenom = '';
        $this->email = '';
        $this->password = '';
        $this->telephone = '';
        $this->cin = '';
        $this->image = '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPass): self
    {
        $this->confirmPassword = $confirmPass;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getCin(): ?string
    {
        return $this->cin;
    }

    public function setCin(string $cin): self
    {
        $this->cin = $cin;

        return $this;
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

    public function getVerified(): ?int
    {
        return $this->verified;
    }

    public function setVerified(int $verified): self
    {
        $this->verified = $verified;
        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUsername()
    {
        return $this->email;
    }


    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        if ($image) {
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }



    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->nom,
            $this->prenom,
            $this->email,
            $this->password,
            $this->telephone,
            $this->cin,
            $this->roles,
            $this->image,
            $this->updatedAt,
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->nom,
            $this->prenom,
            $this->email,
            $this->password,
            $this->telephone,
            $this->cin,
            $this->roles,
            $this->image,
            $this->updatedAt,
            ) = unserialize($serialized);
    }

    /**
     * @return string|null
     */
    public function getVerificationCode(): ?string
    {
        return $this->verificationCode;
    }

    /**
     * @param string|null $verificationCode
     */
    public function setVerificationCode(?string $verificationCode): void
    {
        $this->verificationCode = $verificationCode;
    }





}
