<?php

namespace NicolasJoubert\GrabitBundle\Grabber\Client;

use NicolasJoubert\GrabitBundle\Model\SourceInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

interface ClientInterface
{
    public static function getProxyValue(): string;

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getUrlContent(string $url, SourceInterface $source): ?string;
}
