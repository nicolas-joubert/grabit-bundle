<?php

namespace NicolasJoubert\GrabitBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use NicolasJoubert\GrabitBundle\Model\SourceInterface;

/**
 * @template T of SourceInterface
 *
 * @template-extends EntityRepository<SourceInterface>
 */
class SourceRepository extends EntityRepository implements SourceRepositoryInterface
{
    /**
     * @param class-string<SourceInterface> $className
     */
    public function __construct(EntityManagerInterface $em, string $className)
    {
        parent::__construct($em, $em->getClassMetadata($className));
    }
}
