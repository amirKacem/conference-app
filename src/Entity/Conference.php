<?php

namespace App\Entity;

use App\Repository\ConferenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\String\Slugger\SluggerInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ConferenceRepository::class)]
#[UniqueEntity('slug')]
#[ApiResource(
    operations: [new Get(normalizationContext: ['groups' => self::CONFERENCE_ITEM]),
    new GetCollection(normalizationContext: ['groups' => self::CONFERENCE_LIST])],
    order: ['year' => 'DESC', 'city' => 'ASC'],
    paginationEnabled: false
)]
class Conference
{
    public const CONFERENCE_LIST = "conference:list";

    public const CONFERENCE_ITEM = "conference:item";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::CONFERENCE_LIST, self::CONFERENCE_ITEM])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups([self::CONFERENCE_LIST, self::CONFERENCE_ITEM])]
    private ?string $city = null;

    #[ORM\Column(length: 4)]
    #[Groups([self::CONFERENCE_LIST, self::CONFERENCE_ITEM])]
    private ?string $year = null;

    #[ORM\Column]
    private ?bool $isInternational = null;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'conference', orphanRemoval: true)]
    private Collection $comments;

    #[ORM\Column(length: 255, unique:true)]
    #[Groups([self::CONFERENCE_LIST, self::CONFERENCE_ITEM])]
    private ?string $slug = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getYear(): ?string
    {
        return $this->year;
    }

    public function setYear(string $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function isIsInternational(): ?bool
    {
        return $this->isInternational;
    }

    public function setIsInternational(bool $isInternational): static
    {
        $this->isInternational = $isInternational;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setConference($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getConference() === $this) {
                $comment->setConference(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->city .' '.$this->year;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function computeSlug(SluggerInterface $slugger)
    {
        if(true === empty($this->slug) || '-' === $this->slug) {
            $this->slug = (string) $slugger->slug((string) $this)->lower();
        }
    }
}
