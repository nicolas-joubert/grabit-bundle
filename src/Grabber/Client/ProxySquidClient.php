<?php

namespace NicolasJoubert\GrabitBundle\Grabber\Client;

use NicolasJoubert\GrabitBundle\Model\Enum\SourceProxy;
use NicolasJoubert\GrabitBundle\Model\SourceInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProxySquidClient extends ProxyNoneClient
{
    public function __construct(
        HttpClientInterface $client,
        private readonly string $proxyUrl,
    ) {
        parent::__construct($client);
    }

    #[\Override]
    public static function getProxyValue(): string
    {
        return SourceProxy::SQUID->value;
    }

    #[\Override]
    protected function getClientResultContent(string $url, SourceInterface $source): ?string
    {
        if ('' === $this->proxyUrl || '0' === $this->proxyUrl) {
            throw new \Exception('Cannot use ProxySquidClient without defining grabit.proxy_urls.squid var');
        }

        return parent::getClientResultContent($url, $source);
    }

    #[\Override]
    protected function getOptions(SourceInterface $source): array
    {
        return array_merge(
            parent::getOptions($source),
            ['proxy' => $this->proxyUrl, 'verify_peer' => false, 'verify_host' => false]
        );
    }
}
