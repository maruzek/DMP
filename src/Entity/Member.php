<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MemberRepository::class)
 */
class Member
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="members")
     * @ORM\JoinColumn(nullable=false)
     */
    private $member;

    /**
     * @ORM\ManyToOne(targetEntity=Project::class, inversedBy="members")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $project;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $accepted;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember(): ?User
    {
        return $this->member;
    }

    public function setMember(?User $member): self
    {
        $this->member = $member;

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

    public function getAccepted(): ?string
    {
        return $this->accepted;
    }

    public function setAccepted(string $accepted): self
    {
        $this->accepted = $accepted;

        return $this;
    }
}
