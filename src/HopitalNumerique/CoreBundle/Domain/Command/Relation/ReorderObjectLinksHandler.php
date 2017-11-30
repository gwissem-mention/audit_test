<?php

namespace HopitalNumerique\CoreBundle\Domain\Command\Relation;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\Relation;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\RelationRepository;

class ReorderObjectLinksHandler
{
    /**
     * @var RelationRepository $relationRepository
     */
    protected $relationRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var Relation[]|null $relations
     */
    private $relations = null;

    /**
     * LinkObjectHandler constructor.
     *
     * @param RelationRepository $relationRepository
     * @param EntityManagerInterface $relationRepository
     */
    public function __construct(
        RelationRepository $relationRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->relationRepository = $relationRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param ReorderObjectLinksCommand $command
     */
    public function handle(ReorderObjectLinksCommand $command)
    {
        foreach ($command->orderedObjectsIdentityId as $position => $object) {
            $this->getRelation($command->sourceObjectIdentity, $object['id'])->setOrder($position + 1);
        }

        $this->entityManager->flush();
    }

    /**
     * @param ObjectIdentity $objectIdentity
     * @param string $id
     *
     * @return Relation
     */
    private function getRelation(ObjectIdentity $objectIdentity, $id)
    {
        if (null === $this->relations) {
            $this->relations = $this->relationRepository->getObjectIdentityRelations($objectIdentity);
        }

        foreach ($this->relations as $relation) {
            if ($relation->getTargetObjectIdentity()->getId() === $id) {
                return $relation;
            }
        }

        throw new \LogicException();
    }
}
