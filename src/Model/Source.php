<?php

declare(strict_types=1);

namespace NicolasJoubert\GrabitBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use NicolasJoubert\GrabitBundle\Model\Enum\SourceProxy;
use NicolasJoubert\GrabitBundle\Model\Enum\SourceResultFormat;

/**
 * @phpstan-import-type Headers from \NicolasJoubert\GrabitBundle\Model\SourceInterface
 */
abstract class Source implements \Stringable, SourceInterface
{
    protected ?int $id = null;

    protected string $label = '';

    /**
     * @var array<string>
     */
    protected array $urls = [];

    /**
     * @var ?Headers
     */
    protected ?array $headers = null;

    protected string $template = '';

    protected SourceResultFormat $resultFormat = SourceResultFormat::HTML;

    protected SourceProxy $proxy = SourceProxy::NONE;

    protected bool $stopOnLastUniqueContentId = true;

    protected bool $enabled = true;

    protected int $maxNumberError = 0;

    protected int $countError = 0;

    protected ?string $lastError = null;

    /**
     * @var Collection<int, ExtractedDataInterface>
     */
    protected Collection $extractedDatas;

    public function __construct()
    {
        $this->extractedDatas = new ArrayCollection();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getLabel();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return array<string>
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * @param array<string> $urls
     */
    public function setUrls(array $urls): static
    {
        $this->urls = $urls;

        return $this;
    }

    /**
     * @return ?Headers
     */
    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    /**
     * @param array<string, string> $defaults
     *
     * @return array<string>
     */
    public function formatHeaders(array $defaults = []): array
    {
        $headers = $defaults;
        foreach ($this->getHeaders() ?? [] as $header) {
            $headers[$header['type']] = $header['content'];
        }
        $formatedHeaders = [];
        foreach ($headers as $type => $content) {
            $formatedHeaders[] = sprintf('%s: %s', $type, $content);
        }

        return $formatedHeaders;
    }

    /**
     * @param ?Headers $headers
     */
    public function setHeaders(?array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): static
    {
        $this->template = $template;

        return $this;
    }

    public function getResultFormat(): SourceResultFormat
    {
        return $this->resultFormat;
    }

    public function isJsonResult(): bool
    {
        return SourceResultFormat::JSON === $this->resultFormat;
    }

    public function isXmlResult(): bool
    {
        return SourceResultFormat::XML === $this->resultFormat;
    }

    public function setResultFormat(SourceResultFormat $resultFormat): static
    {
        $this->resultFormat = $resultFormat;

        return $this;
    }

    public function getProxy(): SourceProxy
    {
        return $this->proxy;
    }

    public function setProxy(SourceProxy $proxy): static
    {
        $this->proxy = $proxy;

        return $this;
    }

    public function isStopOnLastUniqueContentId(): bool
    {
        return $this->stopOnLastUniqueContentId;
    }

    public function setStopOnLastUniqueContentId(bool $stopOnLastUniqueContentId): static
    {
        $this->stopOnLastUniqueContentId = $stopOnLastUniqueContentId;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getMaxNumberError(): int
    {
        return $this->maxNumberError;
    }

    public function setMaxNumberError(int $maxNumberError): static
    {
        $this->maxNumberError = $maxNumberError;

        return $this;
    }

    public function getCountError(): int
    {
        return $this->countError;
    }

    public function setCountError(int $countError): static
    {
        $this->countError = $countError;

        return $this;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function setLastError(?string $lastError): static
    {
        $this->lastError = $lastError;

        return $this;
    }

    /**
     * @return Collection<int, ExtractedDataInterface>
     */
    public function getExtractedDatas(): Collection
    {
        return $this->extractedDatas;
    }

    public function extractedDataCount(): int
    {
        return $this->extractedDatas->count();
    }
}
