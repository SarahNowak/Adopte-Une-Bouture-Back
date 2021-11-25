<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"user_browse","user_read", "ads_read","ads_browse", "messages_browse", "messages_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     * @Groups({"user_browse","user_read", "ads_read"})
     * Contraintes de validation
     * @Assert\Email (message="Veuillez saisir une adresse email valide")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=64)
     * @Groups({"user_browse","user_read"})
     * Contraintes de validation
     * @Assert\Length(
     *      min = 6,
     *      max = 100,
     *      minMessage = "Votre message doit faire minimum 6 caractères",
     *      maxMessage = "Votre message doit faire maximum 100 caractères"
     * )
     */
    private $password;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", length=32)
     * @Groups({"user_browse","user_read", "ads_read","ads_browse"})
     * Contraintes de validation
     * @Assert\NotBlank (message="veuillez saisir votre Pseudo")
     */
    private $pseudo;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Groups({"user_browse","user_read"})
     */
    private $adress;

    /**t
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"user_browse","user_read"})
     */
    private $postal_code;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Groups({"user_browse","user_read", "ads_read"})
     */
    private $city;

    /**
     * @ORM\Column(type="array", length=64, nullable=true)
     * @Groups({"user_browse","user_read", "ads_read"})
     */
    private $coordinates  = [null, null];

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Groups({"user_browse","user_read"})
     */
    private $avatar;

    /**
     * @ORM\Column(type="json")
     * @Groups({"user_browse","user_read"})
     */
    private $roles = ['ROLE_USER'];


    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"user_browse","user_read"})
     */
    private $status = 1;

    /**
     * @ORM\OneToMany(targetEntity=Ads::class, mappedBy="users")
     * @Groups({"user_browse","user_read"})
     */
    private $ads;

    /**
     * @ORM\OneToMany(targetEntity=Messages::class, mappedBy="users")
     */
    private $messages;

    /**
     * Table pivot = ManyToMany
     * @ORM\ManyToMany(targetEntity=Ads::class, mappedBy="favoris")
     * @Groups({"user_read","user_favoris"})
     */
    private $favoris;

    /**
     * Il faut toujours initialiser les collections des associations @OneToMany et @ManyToManydans dans le constructeur de l'entité
     */
    public function __construct()
    {
        $this->ads = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->messages = new ArrayCollection();
        $this->favoris = new ArrayCollection();
    }

    /**
     * Converti un objet en string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->pseudo;
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

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): self
    {
        $this->adress = $adress;

        return $this;
    }

    public function getPostalCode(): ?int
    {
        return $this->postal_code;
    }

    public function setPostalCode(int $postal_code): self
    {
        $this->postal_code = $postal_code;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCoordinates(): ?array
    {
        return $this->coordinates;
    }

    public function setCoordinates(?array $coordinates): self
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|Ads[]
     */
    public function getAds(): Collection
    {
        return $this->ads;
    }

    public function addAd(Ads $ad): self
    {
        if (!$this->ads->contains($ad)) {
            $this->ads[] = $ad;
            $ad->setUsers($this);
        }

        return $this;
    }

    public function removeAd(Ads $ad): self
    {
        if ($this->ads->removeElement($ad)) {
            // set the owning side to null (unless already changed)
            if ($ad->getUsers() === $this) {
                $ad->setUsers(null);
            }
        }

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */

    public function getUserIdentifier(): string

    {
        return (string) $this->email;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserName(): string
    {
        return (string) $this->email;
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
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Messages[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Messages $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setUsers($this);
        }

        return $this;
    }

    public function removeMessage(Messages $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getUsers() === $this) {
                $message->setUsers(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Ads[]
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavori(Ads $favori): self
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris[] = $favori;
            $favori->addFavori($this);
        }

        return $this;
    }

    public function removeFavori(Ads $favori): self
    {
        if ($this->favoris->removeElement($favori)) {
            $favori->removeFavori($this);
        }

        return $this;
    }
}
