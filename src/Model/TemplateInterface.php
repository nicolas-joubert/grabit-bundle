<?php

namespace NicolasJoubert\GrabitBundle\Model;

/**
 * @phpstan-type Configuration array{
 *     'container': string,
 *     'contents': array<string, ConfigurationContent|string>,
 * }
 * @phpstan-type ConfigurationContent array{
 *     'type'?: string,
 *     'filter'?: string,
 *     'extract'?: string,
 *     'content'?: string,
 *     'clean'?: string,
 *     'json'?: string,
 *     'concat'?: array<string, array{
 *         'type'?: string,
 *         'filter': string,
 *         'extract'?: string,
 *         'content'?: string,
 *         'clean'?: string,
 *         'json'?: string,
 *     }>,
 *     'fallback'?: array{
 *         'type'?: string,
 *         'filter': string,
 *         'extract'?: string,
 *         'content'?: string,
 *         'clean'?: string,
 *         'json'?: string,
 *     },
 * }
 */
interface TemplateInterface
{
    public function getId(): ?int;

    public function getCode(): string;

    public function getLabel(): string;

    public function setLabel(string $label): static;

    public function setCode(string $code): static;

    /**
     * @return ?Configuration
     */
    public function getConfiguration(): ?array;

    /**
     * @param ?Configuration $configuration
     */
    public function setConfiguration(?array $configuration): static;
}
