<?php

namespace HopitalNumerique\CartBundle\Service\ReportGenerator\Generator;

use HopitalNumerique\CartBundle\Entity\Report;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ReferenceBundle\DependencyInjection\Referencement;
use HopitalNumerique\ReferenceBundle\Repository\EntityHasReferenceRepository;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CartBundle\Model\Report\Person;
use HopitalNumerique\CartBundle\Model\Report\ItemInterface;
use HopitalNumerique\CartBundle\Service\ReportGenerator\ItemGeneratorInterface;

class PersonGenerator implements ItemGeneratorInterface
{
    /**
     * @var Referencement $referencement
     */
    protected $referencement;

    /**
     * @var Entity $entity
     */
    protected $entity;

    /**
     * PersonGenerator constructor.
     *
     * @param Referencement $referencement
     * @param Entity $entity
     */
    public function __construct(Referencement $referencement, Entity $entity)
    {
        $this->referencement = $referencement;
        $this->entity = $entity;
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public function support($object)
    {
        return $object instanceof User;
    }

    /**
     * @param User $person
     * @param Report $report
     *
     * @return ItemInterface
     */
    public function process($person, Report $report)
    {
        $references = [];
        if (!is_null($entityType = $this->entity->getEntityType($person))) {
            $references = $this->referencement->getReferencesTreeOnlyWithEntitiesHasReferences(
                $person->getDomaines(),
                $entityType,
                $person->getId()
            );
        }

        $item = new Person($person, $references);

        return $item;
    }

}
