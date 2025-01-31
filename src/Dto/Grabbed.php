<?php

namespace NicolasJoubert\GrabitBundle\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class Grabbed implements GrabbedInterface
{
    #[Groups(['input'])]
    #[Assert\NotBlank]
    private string $unique = '';

    #[Groups(['input'])]
    #[Assert\NotBlank]
    private string $title = '';

    #[Groups(['input'])]
    private ?\DateTime $publicationDate = null;

    #[Groups(['input'])]
    #[Assert\NotBlank]
    private string $description = '';

    #[Groups(['input'])]
    #[Assert\NotBlank]
    private string $link = '';

    #[Groups(['input'])]
    private ?string $image = null;

    #[\Override]
    public function getUnique(): string
    {
        return $this->unique;
    }

    #[\Override]
    public function setUnique(string $unique): void
    {
        $this->unique = $unique;
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->title;
    }

    #[\Override]
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    #[\Override]
    public function getPublicationDate(): ?\DateTime
    {
        return $this->publicationDate;
    }

    #[\Override]
    public function setPublicationDate(?\DateTime $publicationDate): void
    {
        $this->publicationDate = $publicationDate;
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[\Override]
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    #[\Override]
    public function getLink(): string
    {
        return $this->link;
    }

    #[\Override]
    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    #[\Override]
    public function getImage(): ?string
    {
        return $this->image;
    }

    #[\Override]
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }
}
