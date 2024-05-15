<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\CommentRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => self::COMMENT_ITEM]
        ),
        new GetCollection(
            normalizationContext:['groups' => self::COMMENT_ITEM]
        )
    ],
    order: ['createdAt' => 'DESC'],
    paginationEnabled: false
)]
#[ApiFilter(SearchFilter::class, properties: ['conference' => 'exact'])]
class Comment
{
    public const COMMENT_LIST = "comment:list";

    public const COMMENT_ITEM = "comment:item";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::COMMENT_LIST, self::COMMENT_ITEM])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups([self::COMMENT_LIST, self::COMMENT_ITEM])]
    private ?string $author = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups([self::COMMENT_LIST, self::COMMENT_ITEM])]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups([self::COMMENT_LIST, self::COMMENT_ITEM])]
    private ?string $text = null;

    #[ORM\Column]
    #[Groups([self::COMMENT_LIST, self::COMMENT_ITEM])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([self::COMMENT_LIST, self::COMMENT_ITEM])]
    private ?Conference $conference = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([self::COMMENT_LIST, self::COMMENT_ITEM])]
    private ?string $photoFilename = null;

    #[ORM\Column(nullable: true, options:["default" => "submitted"])]
    #[Groups([self::COMMENT_LIST, self::COMMENT_ITEM])]
    private ?string $state = "submitted";

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue()
    {
        $this->createdAt = new DateTimeImmutable();
    }


    public function getConference(): ?Conference
    {
        return $this->conference;
    }

    public function setConference(?Conference $conference): static
    {
        $this->conference = $conference;

        return $this;
    }

    public function getPhotoFilename(): ?string
    {
        return $this->photoFilename;
    }

    public function setPhotoFilename(?string $photoFilename): static
    {
        $this->photoFilename = $photoFilename;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->getEmail();
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

        return $this;
    }
}
