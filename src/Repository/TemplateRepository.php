<?php

namespace NicolasJoubert\GrabitBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use NicolasJoubert\GrabitBundle\Model\TemplateInterface;

/**
 * @template T of TemplateInterface
 *
 * @template-extends EntityRepository<TemplateInterface>
 */
class TemplateRepository extends EntityRepository implements TemplateRepositoryInterface
{
    /**
     * @param class-string<TemplateInterface> $className
     */
    public function __construct(EntityManagerInterface $em, string $className)
    {
        parent::__construct($em, $em->getClassMetadata($className));
    }
}
