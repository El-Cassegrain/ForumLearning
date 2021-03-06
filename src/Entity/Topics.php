<?php

namespace App\Entity;

use App\Repository\TopicsRepository;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TopicsRepository::class)
 */
class Topics
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="topics")
     * @ORM\JoinColumn(nullable=false)
     */
    private $users;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity=Likes::class, mappedBy="topic", orphanRemoval=true)
     */
    private $likes;

    /**
     * @ORM\OneToMany(targetEntity=Dislikes::class, mappedBy="topic", orphanRemoval=true)
     */
    private $dislikes;

    /**
     * @ORM\OneToMany(targetEntity=Commentaires::class, mappedBy="topic", orphanRemoval=true)
     */
    private $commentaires;

    /**
     * @ORM\OneToMany(targetEntity=Reports::class, mappedBy="topic", orphanRemoval=true)
     */
    private $reports;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->dislikes = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->reports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsers(): ?Users
    {
        return $this->users;
    }

    public function setUsers(?Users $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
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

    /**
     * @return Collection|Likes[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Likes $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->setTopic($this);
        }

        return $this;
    }

    public function removeLike(Likes $like): self
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getTopic() === $this) {
                $like->setTopic(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Dislikes[]
     */
    public function getDislikes(): Collection
    {
        return $this->dislikes;
    }

    public function addDislike(Dislikes $dislike): self
    {
        if (!$this->dislikes->contains($dislike)) {
            $this->dislikes[] = $dislike;
            $dislike->setTopic($this);
        }

        return $this;
    }

    public function removeDislike(Dislikes $dislike): self
    {
        if ($this->dislikes->removeElement($dislike)) {
            // set the owning side to null (unless already changed)
            if ($dislike->getTopic() === $this) {
                $dislike->setTopic(null);
            }
        }

        return $this;
    }

    /**
     * Savoir si le topic a été liké par l'utilisateur
     *
     * @param Users $user
     * @return boolean
     */
    public function likeBy(Users $user) : bool
    {
        foreach ($this->likes as $like) {
            if ($like->getUser() === $user) {
                return true;
            }
        }

        return false;
    }

    public function dislikeBy(Users $user) : bool
    {
        foreach ($this->dislikes as $dislike) {
            if ($dislike->getUser() === $user) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * @return Collection|Commentaires[]
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaires $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setTopic($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaires $commentaire): self
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getTopic() === $this) {
                $commentaire->setTopic(null);
            }
        }

        return $this;
    }

    

    /**
     * @return Collection|Reports[]
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Reports $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports[] = $report;
            $report->setTopic($this);
        }

        return $this;
    }

    public function removeReport(Reports $report): self
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getTopic() === $this) {
                $report->setTopic(null);
            }
        }

        return $this;
    }

    public function description()
    {
        $lg_max = 255; // nombre de caractère max
        $description = strip_tags($this->getText()); // On retire toutes les balises
        
        if (strlen($description) > $lg_max) {
            $description = substr($description, 0, $lg_max) ;
            $last_space = strrpos($description, " ") ;
            $description = substr($description, 0, $last_space)."..." ;
        }
        return $description;
    }

    /**
     * Determine si il faut bloquer l'edition, si ça fait +30 min ou si il y a une interaction sur le topic
     *
     * @return boolean
     */
    public function EditLimit() : bool
    {
        $topicDate = $this->getDate();
        $date = new DateTime();
        $date->add(new DateInterval('PT30M'));
        $likes = $this->getLikes();
        $dislikes = $this->getDislikes();
        $commentaires = $this->getCommentaires();
        $reports = $this->getReports();

        if ((count($likes) === 0)  && (count($dislikes) === 0) && (count($commentaires) === 0) && (count($reports) === 0)) {
            return true;
        }
        if ($topicDate > $date) {
            return true;
        }

        return false;
    }
}
