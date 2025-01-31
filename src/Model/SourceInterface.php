<?php

namespace NicolasJoubert\GrabitBundle\Model;

use Doctrine\Common\Collections\Collection;
use NicolasJoubert\GrabitBundle\Model\Enum\SourceProxy;
use NicolasJoubert\GrabitBundle\Model\Enum\SourceResultFormat;

/**
 * @phpstan-type Headers array<array{
 *      'type': string,
 *      'content': string,
 *  }>
 */
interface SourceInterface
{
    public function getId(): ?int;

    public function getLabel(): string;

    public function setLabel(string $label): static;

    /**
     * @return array<string>
     */
    public function getUrls(): array;

    /**
     * @param array<string> $urls
     */
    public function setUrls(array $urls): static;

    /**
     * @return ?Headers
     */
    public function getHeaders(): ?array;

    /**
     * @param array<string, string> $defaults
     *
     * @return array<string>
     */
    public function formatHeaders(array $defaults = []): array;

    /**
     * @param ?Headers $headers
     */
    public function setHeaders(?array $headers): static;

    public function getTemplate(): string;

    public function setTemplate(string $template): static;

    public function getResultFormat(): SourceResultFormat;

    public function isJsonResult(): bool;

    public function isXmlResult(): bool;

    public function setResultFormat(SourceResultFormat $resultFormat): static;

    public function getProxy(): SourceProxy;

    public function setProxy(SourceProxy $proxy): static;

    public function isStopOnLastUniqueContentId(): bool;

    public function setStopOnLastUniqueContentId(bool $stopOnLastUniqueContentId): static;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): static;

    public function getMaxNumberError(): int;

    public function setMaxNumberError(int $maxNumberError): static;

    public function getCountError(): int;

    public function setCountError(int $countError): static;

    public function getLastError(): ?string;

    public function setLastError(?string $lastError): static;

    /**
     * @return Collection<int, ExtractedDataInterface>
     */
    public function getExtractedDatas(): Collection;

    public function extractedDataCount(): int;
}
