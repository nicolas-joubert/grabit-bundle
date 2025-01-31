<?php

declare(strict_types=1);

namespace NicolasJoubert\GrabitBundle\Model;

use NicolasJoubert\GrabitBundle\Dto\GrabbedInterface;

abstract class ExtractedData implements \Stringable, ExtractedDataInterface
{
    protected ?int $id = null;

    protected SourceInterface $source;

    protected string $uniqueContentId = '';

    protected ?GrabbedInterface $content = null;

    protected \DateTimeInterface $createdAt;

    protected ?\DateTimeInterface $publishedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getUniqueContentId();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSource(): SourceInterface
    {
        return $this->source;
    }

    public function setSource(SourceInterface $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function getUniqueContentId(): string
    {
        return $this->uniqueContentId;
    }

    public function setUniqueContentId(string $uniqueContentId): static
    {
        $this->uniqueContentId = $uniqueContentId;

        return $this;
    }

    public function getContent(): ?GrabbedInterface
    {
        return $this->content;
    }

    public function setContent(?GrabbedInterface $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }
}
