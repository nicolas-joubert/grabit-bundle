<?php

namespace NicolasJoubert\GrabitBundle\Dto;

interface GrabbedInterface
{
    public function getUnique(): string;

    public function setUnique(string $unique): void;

    public function getTitle(): string;

    public function setTitle(string $title): void;

    public function getPublicationDate(): ?\DateTime;

    public function setPublicationDate(?\DateTime $publicationDate): void;

    public function getDescription(): string;

    public function setDescription(string $description): void;

    public function getLink(): string;

    public function setLink(string $link): void;

    public function getImage(): ?string;

    public function setImage(?string $image): void;
}
