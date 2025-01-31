<?php

namespace NicolasJoubert\GrabitBundle\Grabber;

use NicolasJoubert\GrabitBundle\Dto\GrabbedInterface;
use NicolasJoubert\GrabitBundle\Grabber\Client\ClientHandler;
use NicolasJoubert\GrabitBundle\Grabber\Exceptions\AlreadyCrawledException;
use NicolasJoubert\GrabitBundle\Grabber\Exceptions\CrawlerException;
use NicolasJoubert\GrabitBundle\Grabber\Exceptions\GrabException;
use NicolasJoubert\GrabitBundle\Grabber\Exceptions\ValidationException;
use NicolasJoubert\GrabitBundle\Model\ExtractedDataInterface;
use NicolasJoubert\GrabitBundle\Model\SourceInterface;
use NicolasJoubert\GrabitBundle\Repository\ExtractedDataRepositoryInterface;
use NicolasJoubert\GrabitBundle\Validator\Validator;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\RedirectionException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @phpstan-import-type Configuration from \NicolasJoubert\GrabitBundle\Model\TemplateInterface
 * @phpstan-import-type ConfigurationContent from \NicolasJoubert\GrabitBundle\Model\TemplateInterface
 */
class Grabber
{
    private string $currentUrl = '';

    /**
     * @param class-string<GrabbedInterface> $grabbedClassName
     */
    public function __construct(
        private readonly ExtractedDataRepositoryInterface $extractedDataRepository,
        private readonly string $grabbedClassName,
        private readonly Validator $validator,
        private readonly Template $template,
        private readonly ClientHandler $clientHandler,
    ) {}

    /**
     * @return array<array-key, GrabbedInterface>
     *
     * @throws GrabException
     */
    public function grabSource(SourceInterface $source): array
    {
        $templateConfiguration = $this->template->getConfiguration($source->getTemplate());
        if (null === $templateConfiguration) {
            throw new GrabException($source, [], sprintf('Template "%s" not found', $source->getTemplate()));
        }

        $items = [];

        try {
            $uniqueContentIds = $source->isStopOnLastUniqueContentId()
                ? $this->getLastUniqueForSource($source)
                : $this->getUniqueContentIdsForSource($source);
            $allowEmptyContent = false;
            foreach ($source->getUrls() as $this->currentUrl) {
                $crawler = new Crawler(
                    $this->clientHandler->getClient($source->getProxy())->getUrlContent($this->currentUrl, $source),
                    explode('?', $this->currentUrl)[0]
                );

                try {
                    $itemsForUrl = $this->crawlTemplate($crawler, $templateConfiguration, $uniqueContentIds, $allowEmptyContent);
                    $allowEmptyContent = true;
                } catch (AlreadyCrawledException $e) {
                    $itemsForUrl = $e->getGrabbeds();

                    break;
                } finally {
                    $items = array_merge($items, $itemsForUrl ?? []);
                }
            }
        } catch (CrawlerException $e) {
            throw new GrabException($source, array_merge(['Url' => $this->currentUrl], $e->getParameters()), $e->getMessage(), 0, $e);
        } catch (ClientException|RedirectionException|ServerException $e) {
            throw new GrabException($source, ['content' => $e->getResponse()->getContent(false)], $e->getMessage());
        } catch (\Exception $e) {
            throw new GrabException($source, [], $e->getMessage());
        }

        return array_reverse($items);
    }

    /**
     * @param Configuration                        $templateConfiguration
     * @param null|ExtractedDataInterface[]|string $uniqueContentIds
     *
     * @return GrabbedInterface[]
     *
     * @throws CrawlerException
     * @throws AlreadyCrawledException
     */
    protected function crawlTemplate(
        Crawler $crawler,
        array $templateConfiguration,
        null|array|string $uniqueContentIds,
        bool $allowEmptyContent = false,
    ): array {
        try {
            /** @var Crawler[] $nodes */
            $nodes = $crawler->filter($templateConfiguration['container'])->each(fn (Crawler $node, $i) => $node);

            $grabbeds = [];
            $crawlUniqueContentIds = [];
            foreach ($nodes as $i => $node) {
                $contents = [];
                foreach ($templateConfiguration['contents'] as $key => $value) {
                    try {
                        $content = $this->processContent($node, $key, $value);
                    } catch (\InvalidArgumentException $e) {
                        throw new CrawlerException(['Item number' => $i, 'Key' => sprintf('%s (%s)', $key, is_array($value) ? print_r($value, true) : $value), 'HtmlContent' => $node->html()], $e->getMessage(), $crawler);
                    }

                    if (null !== $content) {
                        $contents[$key] = $content;
                    }
                }

                try {
                    $grabbed = $this->transformToGrabbed($contents);
                } catch (ValidationException $e) {
                    throw new CrawlerException(['Item number' => $i, 'HtmlContent' => $node->html()], $e->getMessage(), $crawler);
                }

                if (in_array($grabbed->getUnique(), $crawlUniqueContentIds)) {
                    continue;
                }
                if (is_array($uniqueContentIds)) {
                    if (in_array($grabbed->getUnique(), $uniqueContentIds)) {
                        continue;
                    }
                } elseif ($grabbed->getUnique() === $uniqueContentIds) {
                    throw new AlreadyCrawledException($grabbeds);
                }

                $crawlUniqueContentIds[] = $grabbed->getUnique();

                $grabbeds[] = $grabbed;
            }

            if ([] === $grabbeds && empty($nodes) && !$allowEmptyContent) {
                throw new CrawlerException(['TemplateConfiguration' => $templateConfiguration], 'No content found', $crawler);
            }

            return $grabbeds;
        } catch (PartialDenormalizationException $e) {
            throw new CrawlerException(['Messages' => implode(',', array_map(fn ($exception): string => $exception->getMessage(), $e->getErrors())), 'Data' => $e->getData()], 'Denormalization Error', $crawler);
        }
    }

    /**
     * @param ConfigurationContent|string $value
     */
    protected function processContent(Crawler $node, string $key, array|string $value): ?string
    {
        if (!is_array($value)) {
            $value = ['filter' => $value];
        }
        if (empty($value['filter']) && empty($value['extract'] ?? null)
            && empty($value['content'] ?? null)
            && 'current_url' !== ($value['type'] ?? null)
        ) {
            return null;
        }
        if (!isset($value['type']) || empty($value['type'])) {
            $value['type'] = match ($key) {
                'link' => 'link',
                'image' => 'image',
                default => 'text',
            };
        }

        $currentNode = $node;
        if (!empty($value['filter'])) {
            $currentNode = $currentNode->filter($value['filter']);
        }

        if (0 === $currentNode->count()) {
            $content = '';
        } else {
            if (!empty($value['content'])) {
                $content = 'now' === $value['content']
                    ? (new \DateTime())->format('c')
                    : $value['content'];
            } elseif ('link' === $value['type']) {
                $content = $currentNode->link()->getUri();
            } elseif ('image' === $value['type']) {
                $content = $currentNode->image()->getUri();
            } elseif (!empty($value['extract'])) {
                $content = $currentNode->attr($value['extract']);
            } elseif ('text' === $value['type']) {
                $content = $currentNode->text();
            } elseif ('current_url' === $value['type']) {
                $content = $this->currentUrl;
            } else {
                return null;
            }

            if ('timestamp' === $value['type']) {
                $content = (new \DateTime())->setTimestamp((int) $content)->format('c');
            }

            if (!empty($value['json']) && !empty($content)) {
                /** @var array<string, mixed> $json */
                $json = json_decode($content, true);
                foreach (explode('.', $value['json']) as $key) {
                    /** @var array<string, mixed> $json */
                    if (isset($json[$key])) {
                        $json = $json[$key];
                    }
                }
                $content = is_string($json) ? $json : '';
            }

            if (!empty($value['clean']) && !empty($content)) {
                $content = str_replace($value['clean'], '', $content);
            }
        }

        if (isset($value['concat'])) {
            foreach ($value['concat'] as $concatKey => $concatValue) {
                $content .= $this->processContent($node, $concatKey, $concatValue);
            }
        }

        return $content;
    }

    /**
     * @param array<string, string> $contents
     *
     * @throws PartialDenormalizationException
     * @throws ValidationException
     */
    protected function transformToGrabbed(array $contents): GrabbedInterface
    {
        $serializer = new Serializer([
            new DateTimeNormalizer(),
            new ArrayDenormalizer(),
            new ObjectNormalizer(
                new ClassMetadataFactory(new AttributeLoader()),
                null,
                null,
                new ReflectionExtractor()
            ),
        ]);

        /**
         * @var GrabbedInterface $grabbed
         */
        $grabbed = $serializer->denormalize(
            $contents,
            $this->grabbedClassName,
            null,
            [
                'groups' => ['input'],
                DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
                AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
            ],
        );

        $this->validator->validate($grabbed);

        return $grabbed;
    }

    private function getLastUniqueForSource(SourceInterface $source): ?string
    {
        return $this->extractedDataRepository->getLastUniqueForSource($source);
    }

    /**
     * @return ExtractedDataInterface[]
     */
    private function getUniqueContentIdsForSource(SourceInterface $source): array
    {
        return $this->extractedDataRepository->getUniqueContentIdsForSource($source);
    }
}
