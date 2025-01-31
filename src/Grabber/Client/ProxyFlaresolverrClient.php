<?php

namespace NicolasJoubert\GrabitBundle\Grabber\Client;

use NicolasJoubert\GrabitBundle\Model\Enum\SourceProxy;
use NicolasJoubert\GrabitBundle\Model\SourceInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProxyFlaresolverrClient extends BaseClient
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $proxyUrl,
    ) {}

    #[\Override]
    public static function getProxyValue(): string
    {
        return SourceProxy::FLARESOLVERR->value;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[\Override]
    public function getClientResultContent(string $url, SourceInterface $source): ?string
    {
        if ('' === $this->proxyUrl || '0' === $this->proxyUrl) {
            throw new \Exception('Cannot use ProxyFlaresolverrClient without defining grabit.proxy_urls.flaresolverr var');
        }

        /** @var array{
         *     'solution'?: array{'response': string},
         *     'status': string,
         *     'message': string,
         *     'startTimestamp': string,
         *     'endTimestamp': string,
         *     'version': string,
         * } $response
         */
        $response = json_decode(
            $this->client->request(
                'POST',
                $this->proxyUrl,
                array_merge(
                    $this->getOptions($source),
                    [
                        'json' => [
                            'cmd' => 'request.get',
                            'url' => $url,
                            'session' => 'grabit',
                        ],
                    ]
                ),
            )->getContent(),
            true
        );

        $content = $response['solution']['response'] ?? null;

        if (null !== $content && ($source->isJsonResult() || $source->isXmlResult())) {
            $crawler = new Crawler($content);
            $content = $crawler->filter($source->isJsonResult() ? 'body>pre' : '#webkit-xml-viewer-source-xml')->html();
        }

        return $content;
    }

    #[\Override]
    protected function getBaseHeaders(SourceInterface $source): array
    {
        $headers = parent::getBaseHeaders($source);
        $headers['Content-Type'] = 'application/json';

        return $headers;
    }
}
