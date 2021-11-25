<?php

namespace App\Entity;

use App\Repository\AdsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AdsRepository::class)
 */
class Ads
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"ads_browse","ads_read", "messages_browse", "messages_read", "user_browse", "user_read", "plants_read","plants_browse"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * @Groups({"ads_browse","ads_read","user_browse","user_read"})
     * Contraintes de validation
     * @Assert\NotBlank (message="Veuillez indiquer un titre à votre annonce")
     */
    private $plant_ads;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Groups({"ads_browse","ads_read","user_browse","user_read"})
     * Contraintes de validation
     * @Assert\NotBlank (message="Veuillez indiquer une ville")
     */
    private $city;

    /**
     * @ORM\Column(type="array", length=64, nullable=true)
     * @Groups({"ads_browse","ads_read","user_browse","user_read"})
     */
    private $coordinates = [null, null];

    /**
     * @ORM\Column(type="integer")
     * @Groups({"ads_browse","ads_read","user_browse","user_read"})
     * Contraintes de validation
     * @Assert\NotBlank (message="Veuillez indiquer la quantité que vous souhaitez donner")
     */
    private $quantity;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"ads_browse","ads_read","user_browse","user_read"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Groups({"ads_browse","ads_read","user_browse","user_read"})
     */
    private $image;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * Propriétaire de la relation = ManyToOne
     * Cascade "persist" permet à Doctrine de gérer la persistence (ajout, modification, suppression) des collections d’objets présents dans la relation inverse
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="ads", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"ads_browse","ads_read","user_browse","user_read"})
     * Contraintes de validation
     * @Assert\NotBlank (message="Choisissez votre catégorie")
     */
    private $category;

    /**
     * Propriétaire de la relation = ManyToOne
     * Cascade "persist" permet à Doctrine de gérer la persistence (ajout, modification, suppression) des collections d’objets présents dans la relation inverse
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="ads", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"ads_browse","ads_read"})
     */
    private $users;

    /**
     * Propriétaire de la relation = ManyToOne
     * @ORM\ManyToOne(targetEntity=Growth::class, inversedBy="ads")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"ads_browse","ads_read"})
     * Contraintes de validation
     * @Assert\NotBlank (message="Choisissez le stade évolutif")
     */
    private $growths;

    /**
     * @ORM\OneToMany(targetEntity=Messages::class, mappedBy="ads")
     */
    private $messages;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"ads_browse","ads_read", "user_read","user_browse"})
     */
    private $status = 1;

    /**
     * Table pivot = ManyToMany
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="favoris")
     * @Groups({"user_favoris"})
     */
    private $favoris;

    /**
     * Propriétaire de la relation = ManyToOne
     * @ORM\ManyToOne(targetEntity=Plants::class, inversedBy="ads")
     */
    private $plants;

    /**
     *  Il faut toujours initialiser les collections des associations @OneToMany et @ManyToManydans dans le constructeur de l'entité
     */
    public function __construct()
    {
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
        return $this->plant_ads;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlantAds(): ?string
    {
        return $this->plant_ads;
    }

    public function setPlantAds(string $plant_ads): self
    {
        $this->plant_ads = $plant_ads;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function getGrowths(): ?Growth
    {
        return $this->growths;
    }

    public function setGrowths(?Growth $growths): self
    {
        $this->growths = $growths;

        return $this;
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
            $message->setAds($this);
        }

        return $this;
    }

    public function removeMessage(Messages $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getAds() === $this) {
                $message->setAds(null);
            }
        }

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
     * @return Collection|User[]
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavori(User $favori): self
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris[] = $favori;
        }

        return $this;
    }

    public function removeFavori(User $favori): self
    {
        $this->favoris->removeElement($favori);

        return $this;
    }

    public function getPlants(): ?Plants
    {
        return $this->plants;
    }

    public function setPlants(?Plants $plants): self
    {
        $this->plants = $plants;

        return $this;
    }

}
