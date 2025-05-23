<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $class;

    /**
     * @ORM\Column(type="string", length=8)
     */
    private $tag;

    /**
     * @ORM\Column(type="string", length=6)
     */
    private $role = "user";

    /**
     * @ORM\Column(type="string", length=140, nullable=true)
     * @Assert\Length(
     *      max = 140
     * )
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=Project::class, mappedBy="admin")
     */
    private $projects;

    /**
     * @ORM\OneToMany(targetEntity=Follow::class, mappedBy="follower")
     */
    private $follows;

    /**
     * @ORM\OneToMany(targetEntity=Member::class, mappedBy="member")
     */
    private $members;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="author")
     */
    private $posts;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image = "default.png";

    /**
     * @ORM\OneToMany(targetEntity=Media::class, mappedBy="uploader")
     */
    private $media;

    /**
     * @ORM\OneToMany(targetEntity=ProjectAdmin::class, mappedBy="user")
     */
    private $projectAdmins;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="admin", orphanRemoval=true)
     */
    private $events;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $firstLogin;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deactivated = false;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->follows = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->media = new ArrayCollection();
        $this->projectAdmins = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

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

    /**
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setAdmin($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getAdmin() === $this) {
                $project->setAdmin(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Follow[]
     */
    public function getFollows(): Collection
    {
        return $this->follows;
    }

    public function addFollow(Follow $follow): self
    {
        if (!$this->follows->contains($follow)) {
            $this->follows[] = $follow;
            $follow->setFollower($this);
        }

        return $this;
    }

    public function removeFollow(Follow $follow): self
    {
        if ($this->follows->removeElement($follow)) {
            // set the owning side to null (unless already changed)
            if ($follow->getFollower() === $this) {
                $follow->setFollower(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Member[]
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Member $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
            $member->setMember($this);
        }

        return $this;
    }

    public function removeMember(Member $member): self
    {
        if ($this->members->removeElement($member)) {
            // set the owning side to null (unless already changed)
            if ($member->getMember() === $this) {
                $member->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

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
            $medium->setUploader($this);
        }

        return $this;
    }

    public function removeMedium(Media $medium): self
    {
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getUploader() === $this) {
                $medium->setUploader(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProjectAdmin[]
     */
    public function getProjectAdmins(): Collection
    {
        return $this->projectAdmins;
    }

    public function addProjectAdmin(ProjectAdmin $projectAdmin): self
    {
        if (!$this->projectAdmins->contains($projectAdmin)) {
            $this->projectAdmins[] = $projectAdmin;
            $projectAdmin->setUser($this);
        }

        return $this;
    }

    public function removeProjectAdmin(ProjectAdmin $projectAdmin): self
    {
        if ($this->projectAdmins->removeElement($projectAdmin)) {
            // set the owning side to null (unless already changed)
            if ($projectAdmin->getUser() === $this) {
                $projectAdmin->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setAdmin($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getAdmin() === $this) {
                $event->setAdmin(null);
            }
        }

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getFirstLogin(): ?\DateTimeInterface
    {
        return $this->firstLogin;
    }

    public function setFirstLogin(?\DateTimeInterface $firstLogin): self
    {
        $this->firstLogin = $firstLogin;

        return $this;
    }

    public function getDeactivated()
    {
        return $this->deactivated;
    }

    public function setDeactivated($deactivated): self
    {
        $this->deactivated = $deactivated;

        return $this;
    }
}
