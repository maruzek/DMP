<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $project;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $text;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $privacy;

    /**
     * @ORM\OneToMany(targetEntity=Media::class, mappedBy="post")
     */
    private $media;

    /**
     * @ORM\OneToMany(targetEntity=Seen::class, mappedBy="post", orphanRemoval=true)
     */
    private $seens;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted;

    public function __construct()
    {
        $this->media = new ArrayCollection();
        $this->seens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getPrivacy(): ?string
    {
        return $this->privacy;
    }

    public function setPrivacy(string $privacy): self
    {
        $this->privacy = $privacy;

        return $this;
    }

    /**
     * @return Collection|Media[]
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(Media $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media[] = $medium;
            $medium->setPost($this);
        }

        return $this;
    }

    public function removeMedium(Media $medium): self
    {
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getPost() === $this) {
                $medium->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Seen[]
     */
    public function getSeens(): Collection
    {
        return $this->seens;
    }

    public function addSeen(Seen $seen): self
    {
        if (!$this->seens->contains($seen)) {
            $this->seens[] = $seen;
            $seen->setPost($this);
        }

        return $this;
    }

    public function removeSeen(Seen $seen): self
    {
        if ($this->seens->removeElement($seen)) {
            // set the owning side to null (unless already changed)
            if ($seen->getPost() === $this) {
                $seen->setPost(null);
            }
        }

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }
}
