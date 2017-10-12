<?php

namespace HopitalNumerique\CoreBundle\Domain\Command\Relation;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\ObjectIdentityRepository;
use HopitalNumerique\CoreBundle\Repository\ObjectIdentity\RelationRepository;

class LinkObjectHandler
{
    /**
     * @var ObjectIdentityRepository $objectIdentityRepository
     */
    protected $objectIdentityRepository;

    /**
     * @var RelationRepository $relationRepository
     */
    protected $relationRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * LinkObjectHandler constructor.
     *
     * @param ObjectIdentityRepository $objectIdentityRepository
     * @param RelationRepository $relationRepository
     * @param EntityManagerInterface $objectIdentityRepository
     */
    public function __construct(
        ObjectIdentityRepository $objectIdentityRepository,
        RelationRepository $relationRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->objectIdentityRepository = $objectIdentityRepository;
        $this->relationRepository = $relationRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param LinkObjectCommand $command
     */
    public function handle(LinkObjectCommand $command)
    {
        $source = $this->createObjectIdentity(ObjectIdentity::createFromDomainObject($command->sourceObject));
        $target = $this->createObjectIdentity(ObjectIdentity::createFromDomainObject($command->targetObject));

        $this->relationRepository->addRelation($source, $target);

        $this->entityManager->flush();
    }

    /**
     * @param ObjectIdentity $objectIdentity
     *
     * @return ObjectIdentity
     */
    private function createObjectIdentity(ObjectIdentity $objectIdentity)
    {
        /** @var ObjectIdentity $persistedObject */
        if ($persistedObject = $this->objectIdentityRepository->find($objectIdentity->getId())) {
            return $persistedObject;
        }

        $this->entityManager->persist($objectIdentity);
        $this->entityManager->flush($objectIdentity);

        return $objectIdentity;
    }
}
