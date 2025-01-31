<?php

namespace NicolasJoubert\GrabitBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use NicolasJoubert\GrabitBundle\Dto\GrabbedInterface;
use NicolasJoubert\GrabitBundle\Grabber\Exceptions\ValidationException;
use NicolasJoubert\GrabitBundle\Model\ExtractedDataInterface;
use NicolasJoubert\GrabitBundle\Model\SourceInterface;
use NicolasJoubert\GrabitBundle\Validator\Validator;

class ExtractedDataManager
{
    private const int FLUSH_AFTER_COUNT = 10;

    private int $persistCount = 0;

    /**
     * @param class-string<ExtractedDataInterface> $extractedDataClassName
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly string $extractedDataClassName,
        private readonly Validator $validator,
    ) {}

    /**
     * @throws ValidationException
     */
    public function createWithGrabbed(GrabbedInterface $grabbed, SourceInterface $source): void
    {
        $extractedData = (new $this->extractedDataClassName())
            ->setSource($source)
            ->setUniqueContentId($grabbed->getUnique())
            ->setContent($grabbed)
            ->setPublishedAt($grabbed->getPublicationDate())
        ;

        $this->validator->validate($extractedData);
        $this->persist($extractedData);
    }

    public function flushRemaining(): void
    {
        $this->em->flush();
    }

    private function persist(object $object): void
    {
        $this->em->persist($object);
        ++$this->persistCount;
        if ($this->persistCount >= self::FLUSH_AFTER_COUNT) {
            $this->em->flush();
            $this->persistCount = 0;
        }
    }
}
