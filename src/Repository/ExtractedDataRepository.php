<?php

namespace NicolasJoubert\GrabitBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use NicolasJoubert\GrabitBundle\Model\ExtractedDataInterface;
use NicolasJoubert\GrabitBundle\Model\SourceInterface;

/**
 * @template T of ExtractedDataInterface
 *
 * @template-extends EntityRepository<ExtractedDataInterface>
 */
class ExtractedDataRepository extends EntityRepository implements ExtractedDataRepositoryInterface
{
    /**
     * @param class-string<ExtractedDataInterface> $className
     */
    public function __construct(EntityManagerInterface $em, string $className)
    {
        parent::__construct($em, $em->getClassMetadata($className));
    }

    public function getLastUniqueForSource(SourceInterface $source): ?string
    {
        /** @var array{unique_content_id: string} $result */
        $result = $this->createQueryBuilder('ed')
            ->select('ed.uniqueContentId')
            ->where('ed.source = :source')
            ->setParameter('source', $source)
            ->orderBy('ed.createdAt', 'DESC')
            ->addOrderBy('ed.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleColumnResult()
        ;

        return array_shift($result);
    }

    public function getUniqueContentIdsForSource(SourceInterface $source): array
    {
        // @phpstan-ignore return.type
        return $this->createQueryBuilder('ed')
            ->select('ed.uniqueContentId')
            ->where('ed.source = :source')
            ->setParameter('source', $source)
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }
}
