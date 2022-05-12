<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    collectionOperations: ['get' => ['normalization_context' => ['groups' => 'comment:list']]],
    itemOperations: ['get' => ['normalization_context' => ['groups' => 'comment:item']]],
    order: ['createdAt' => 'DESC'],
    paginationEnabled: false
)]
#[ApiFilter(SearchFilter::class, properties: ['conference' => 'exact'])]
class Comment
{

    const  SUBMITTED = 'submitted';
    const  PUBLISHED = 'published';
    const  SPAM = 'spam';



    const ACCEPT = 'accept';
    const REJECT_SPAM = 'reject_spam';
    const REJECT_HAM = 'reject_ham';
    const REJECT = 'reject';
    const MIGHT_BE_SPAM = 'might_be_spam';
    const PUBLISH_HAM = 'publish_ham';
    const PUBLISH = 'publish';
    const OPTIMIZE = 'optimize';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['comment:list', 'comment:item'])]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Groups(['comment:list', 'comment:item'])]
    private string $author;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Groups(['comment:list', 'comment:item'])]
    private string $text;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['comment:list', 'comment:item'])]
    private string $email;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['comment:list', 'comment:item'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: Conference::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['comment:list', 'comment:item'])]
    private ?Conference $conference;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['comment:list', 'comment:item'])]
    private ?string $photoFilename = '';

    #[ORM\Column(type: 'string', length: 255, options: ["default" => self::SUBMITTED])]
    #[Groups(['comment:list', 'comment:item'])]
    private $state = self::SUBMITTED;

    public function __toString(): string
    {
        return (string) $this->getEmail();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getConference(): ?Conference
    {
        return $this->conference;
    }

    public function setConference(?Conference $conference): self
    {
        $this->conference = $conference;

        return $this;
    }

    public function getPhotoFilename(): ?string
    {
        return $this->photoFilename;
    }

    public function setPhotoFilename(string $photoFilename): self
    {
        $this->photoFilename = $photoFilename;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }
}
