<?php

namespace NicolasJoubert\GrabitBundle\Grabber\Client;

use NicolasJoubert\GrabitBundle\Model\Enum\SourceProxy;
use NicolasJoubert\GrabitBundle\Model\SourceInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProxyNoneClient extends BaseClient
{
    public function __construct(
        private readonly HttpClientInterface $client,
    ) {}

    #[\Override]
    public static function getProxyValue(): string
    {
        return SourceProxy::NONE->value;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[\Override]
    protected function getClientResultContent(string $url, SourceInterface $source): ?string
    {
        sleep(3);

        return $this->client->request('GET', $url, $this->getOptions($source))->getContent();
    }
}
