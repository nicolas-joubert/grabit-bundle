<?php

namespace NicolasJoubert\GrabitBundle\Repository;

use Doctrine\Persistence\ObjectRepository;
use NicolasJoubert\GrabitBundle\Model\ExtractedDataInterface;
use NicolasJoubert\GrabitBundle\Model\SourceInterface;

/**
 * @extends ObjectRepository<ExtractedDataInterface>
 */
interface ExtractedDataRepositoryInterface extends ObjectRepository
{
    public function getLastUniqueForSource(SourceInterface $source): ?string;

    /**
     * @return ExtractedDataInterface[]
     */
    public function getUniqueContentIdsForSource(SourceInterface $source): array;
}
