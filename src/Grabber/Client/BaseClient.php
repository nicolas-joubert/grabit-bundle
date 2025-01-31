<?php

namespace NicolasJoubert\GrabitBundle\Grabber\Client;

use NicolasJoubert\GrabitBundle\Model\SourceInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

abstract class BaseClient implements ClientInterface
{
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[\Override]
    public function getUrlContent(string $url, SourceInterface $source): ?string
    {
        $content = $this->getClientResultContent($url, $source);

        if ($source->isJsonResult()) {
            $content = (new Serializer([new ObjectNormalizer()], [new XmlEncoder()]))
                ->encode($this->getArrayFromJson($content), 'xml')
            ;
        }

        return $content;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    abstract protected function getClientResultContent(string $url, SourceInterface $source): ?string;

    /**
     * @return array{'headers': array<string>}
     */
    protected function getOptions(SourceInterface $source): array
    {
        return ['headers' => $source->formatHeaders($this->getBaseHeaders($source))];
    }

    /**
     * @return array<string, string>
     */
    protected function getBaseHeaders(SourceInterface $source): array
    {
        return [
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/png,image/svg+xml,*/*;q=0.8',
            'Accept-Language' => 'fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'Pragma' => 'no-cache',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'none',
            'Upgrade-Insecure-Requests' => '1',
            'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:129.0) Gecko/20100101 Firefox/129.0',
        ];
    }

    /**
     * @return null|array<string, mixed>
     */
    private function getArrayFromJson(?string $content): ?array
    {
        if (null === $content) {
            return null;
        }

        /** @var array<mixed> $arrayContent */
        $arrayContent = json_decode($content, true);

        return $this->cleanKeys(array_keys($arrayContent), array_values($arrayContent));
    }

    /**
     * @param array<string> $arrayKeys
     * @param array<mixed>  $arrayValues
     *
     * @return array<string, mixed>
     */
    private function cleanKeys(array $arrayKeys, array $arrayValues): array
    {
        $arrayKeys = array_map(function (string $value): string {
            if (in_array($value[0], range(0, 9))) {
                $value = '_'.$value;
            }

            return str_replace(['/', '\\', '<', '>'], '_', $value);
        }, $arrayKeys);

        foreach ($arrayValues as $key => $value) {
            if (is_array($value)) {
                $arrayValues[$key] = $this->cleanKeys(array_keys($value), array_values($value));
            }
        }

        return array_combine($arrayKeys, $arrayValues);
    }
}
