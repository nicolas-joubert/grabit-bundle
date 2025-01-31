<?php

declare(strict_types=1);

namespace NicolasJoubert\GrabitBundle\Model;

/**
 * @phpstan-import-type Configuration from \NicolasJoubert\GrabitBundle\Model\TemplateInterface
 */
abstract class Template implements \Stringable, TemplateInterface
{
    protected ?int $id = null;

    protected string $code = '';

    protected string $label = '';

    /**
     * @var ?Configuration
     */
    protected ?array $configuration = null;

    #[\Override]
    public function __toString(): string
    {
        return $this->getLabel();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return ?Configuration
     */
    public function getConfiguration(): ?array
    {
        return $this->configuration;
    }

    /**
     * @param ?Configuration $configuration
     */
    public function setConfiguration(?array $configuration): static
    {
        $this->configuration = $configuration;

        return $this;
    }
}
