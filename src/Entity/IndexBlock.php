<?php

namespace App\Entity;

use App\Repository\IndexBlockRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IndexBlockRepository::class)
 */
class IndexBlock
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class)
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity=Post::class)
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $post;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $type;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $added;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAdded(): ?\DateTimeInterface
    {
        return $this->added;
    }

    public function setAdded(?\DateTimeInterface $added): self
    {
        $this->added = $added;

        return $this;
    }
}
