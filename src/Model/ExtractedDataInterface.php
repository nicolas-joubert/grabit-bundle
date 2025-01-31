<?php

namespace NicolasJoubert\GrabitBundle\Model;

use NicolasJoubert\GrabitBundle\Dto\GrabbedInterface;

interface ExtractedDataInterface
{
    public function getId(): ?int;

    public function getSource(): SourceInterface;

    public function setSource(SourceInterface $source): static;

    public function getUniqueContentId(): string;

    public function setUniqueContentId(string $uniqueContentId): static;

    public function getContent(): ?GrabbedInterface;

    public function setContent(?GrabbedInterface $content): static;

    public function getCreatedAt(): \DateTimeInterface;

    public function getPublishedAt(): ?\DateTimeInterface;

    public function setPublishedAt(?\DateTimeInterface $publishedAt): static;
}
