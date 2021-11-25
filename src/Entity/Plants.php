<?php

namespace App\Entity;

use App\Repository\PlantsRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PlantsRepository::class)
 */
class Plants
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"plants_read","plants_browse"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * @Groups({"plants_read","plants_browse"})
     */
    private $name;

    /**
     * Propriétaire de la relation = ManyToOne
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="plants")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"plants_read","plants_browse"})
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Groups({"plants_read","plants_browse"})
     */
    private $variety;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"plants_read","plants_browse"})
     */
    private $difficulty;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"plants_read","plants_browse"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Groups({"plants_read","plants_browse"})
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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=Ads::class, mappedBy="plants")
     * @Groups({"plants_read","plants_browse"})
     */
    private $ads;

    /**
     *  Il faut toujours initialiser les collections des associations @OneToMany et @ManyToManydans dans le constructeur de l'entité
     */
    public function __construct()
    {
        $this->ads = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getVariety(): ?string
    {
        return $this->variety;
    }

    public function setVariety(?string $variety): self
    {
        $this->variety = $variety;

        return $this;
    }

    public function getDifficulty(): ?int
    {
        return $this->difficulty;
    }

    public function setDifficulty(?int $difficulty): self
    {
        $this->difficulty = $difficulty;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
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
            $ad->setPlants($this);
        }

        return $this;
    }

    public function removeAd(Ads $ad): self
    {
        if ($this->ads->removeElement($ad)) {
            // set the owning side to null (unless already changed)
            if ($ad->getPlants() === $this) {
                $ad->setPlants(null);
            }
        }

        return $this;
    }
}
