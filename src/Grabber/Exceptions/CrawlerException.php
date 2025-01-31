<?php

namespace NicolasJoubert\GrabitBundle\Grabber\Exceptions;

use Symfony\Component\DomCrawler\Crawler;

class CrawlerException extends \Exception
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private readonly array $parameters = [],
        string $message = '',
        private readonly ?Crawler $crawler = null,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getCrawler(): ?Crawler
    {
        return $this->crawler;
    }
}
