<?php

namespace App\Entity;

use App\Repository\FollowRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FollowRepository::class)
 */
class Follow
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="follows")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $follower;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="follows")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $project;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $followed;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFollower(): ?User
    {
        return $this->follower;
    }

    public function setFollower(?User $follower): self
    {
        $this->follower = $follower;

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

    public function getFollowed(): ?\DateTimeInterface
    {
        return $this->followed;
    }

    public function setFollowed(?\DateTimeInterface $followed): self
    {
        $this->followed = $followed;

        return $this;
    }
}
