<?php

namespace NicolasJoubert\GrabitBundle\Grabber;

use NicolasJoubert\GrabitBundle\Repository\TemplateRepositoryInterface;

/**
 * @phpstan-import-type Configuration from \NicolasJoubert\GrabitBundle\Model\TemplateInterface
 */
class Template
{
    /**
     * @var array<string, string>
     */
    private array $templateChoices = [];

    /**
     * @param array<string, ?Configuration> $templates
     */
    public function __construct(
        private readonly TemplateRepositoryInterface $templateRepository,
        private array $templates,
    ) {
        /** @var string $keys */
        foreach (array_keys($this->templates) as $keys) {
            $this->templateChoices[$keys] = 'template.list.'.$keys;
        }

        foreach ($this->templateRepository->findAll() as $entityTemplate) {
            $this->templates[$entityTemplate->getCode()] = $entityTemplate->getConfiguration();
            $this->templateChoices[$entityTemplate->getCode()] = $entityTemplate->getLabel();
        }
    }

    /**
     * @return array<string, string>
     */
    public function getTemplateChoices(): array
    {
        return $this->templateChoices;
    }

    /**
     * @return ?Configuration
     */
    public function getConfiguration(string $id): ?array
    {
        return $this->templates[$id] ?? null;
    }
}
