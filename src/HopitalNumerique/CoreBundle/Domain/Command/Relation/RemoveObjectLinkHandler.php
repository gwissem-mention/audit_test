<?php

namespace HopitalNumerique\CoreBundle\Domain\Command\Relation;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\RelationRepository;

class RemoveObjectLinkHandler
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
     * RemoveObjectLinkHandler constructor.
     *
     * @param RelationRepository $relationRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(RelationRepository $relationRepository, EntityManagerInterface $entityManager)
    {
        $this->relationRepository = $relationRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param RemoveObjectLinkCommand $command
     */
    public function handle(RemoveObjectLinkCommand $command)
    {
        $this->entityManager->remove(
                $this->relationRepository->findOneBy([
                'sourceObjectIdentity' => $command->sourceObjectIdentity,
                'targetObjectIdentity' => $command->targetObjectIdentity,
            ])
        );

        $this->entityManager->flush();
    }
}
