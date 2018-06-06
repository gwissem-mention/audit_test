<?php

namespace HopitalNumerique\StatBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\StatBundle\Entity\GuidedSearchStat;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Allows to log statistics about guided search in database
 * @see GuidedSearchStat
 */
class GuidedSearchStatLogger
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Domaine $domain
     * @param int $entryId identifier for RechercheParcoursGestion object
     * @param int $pathId identifier for RechercheParcours object
     * @param int $pathStepId identifier for RechercheParcoursDetails object
     * @param int $pathSubStepId identifier for Reference object
     * @param User|null $user
     * @param string|null $sessionIdentifier
     */
    public function logStat(
        Domaine $domain,
        $entryId,
        $pathId = null,
        $pathStepId = null,
        $pathSubStepId = null,
        User $user = null,
        $sessionIdentifier = null
    ) {
        $guidedSearchStat = new GuidedSearchStat();

        try {
            /** @var RechercheParcoursGestion $entry */
            $entry = $this->entityManager->getReference(RechercheParcoursGestion::class, $entryId);
            /** @var RechercheParcours $path */
            $path = $pathId ? $this->entityManager->getReference(RechercheParcours::class, $pathId) : null;
            /** @var RechercheParcoursDetails $pathStep */
            $pathStep = $pathStepId ? $this->entityManager->getReference(RechercheParcoursDetails::class, $pathStepId) : null;
            /** @var Reference $pathSubStep */
            $pathSubStep = $pathSubStepId ? $this->entityManager->getReference(Reference::class, $pathSubStepId) : null;

            $guidedSearchStat->setUser($user);
            $guidedSearchStat->setSessionIdentifier($sessionIdentifier);
            $guidedSearchStat->setDomain($domain);
            $guidedSearchStat->setEntry($entry);
            $guidedSearchStat->setPath($path);
            $guidedSearchStat->setPathStep($pathStep);
            $guidedSearchStat->setPathSubStep($pathSubStep);
            $guidedSearchStat->setDate(new \DateTime());

            $this->entityManager->persist($guidedSearchStat);
            $this->entityManager->flush();
        } catch (\Exception $exception) {}
    }
}
