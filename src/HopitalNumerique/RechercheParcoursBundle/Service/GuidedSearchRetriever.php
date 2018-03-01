<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use HopitalNumerique\RechercheParcoursBundle\Repository\GuidedSearchRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GuidedSearchRetriever
{
    const SESSION_ID = 'guided_search';

    /**
     * @var GuidedSearchRepository $guidedSearchRepository
     */
    protected $guidedSearchRepository;

    /**
     * @var SessionInterface $session
     */
    protected $session;

    /**
     * @var TokenStorageInterface $tokenStorage
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationCheckerInterface $authorizationChecker
     */
    protected $authorizationChecker;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * GuidedSearchRetriever constructor.
     *
     * @param GuidedSearchRepository $guidedSearchRepository
     * @param SessionInterface $session
     * @param TokenStorageInterface $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        GuidedSearchRepository $guidedSearchRepository,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityManagerInterface $entityManager
    ) {
        $this->guidedSearchRepository = $guidedSearchRepository;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->entityManager = $entityManager;
    }

    /**
     * Retrieve GuidedSearch if
     * 1. Owner exists
     * 2. Session if defined
     * 3. Or create a new one

     * @param RechercheParcours $guidedSearchReference
     *
     * @return GuidedSearch
     */
    public function retrieve(RechercheParcours $guidedSearchReference)
    {
        if (
            $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') &&
            !is_null($guidedSearch = $this->guidedSearchRepository->findLatestByOwnerAndGuidedSearchReference($this->tokenStorage->getToken()->getUser(), $guidedSearchReference))
        ) {
            return $guidedSearch;
        }

        if (
            !is_null($guidedSearchId = $this->session->get(self::SESSION_ID)) &&
            !is_null($guidedSearch = $this->guidedSearchRepository->findOneBy(['id' => $guidedSearchId, 'guidedSearchReference' => $guidedSearchReference->getId()]))
        ) {
            return $guidedSearch;
        }

        return $this->createNewOne($guidedSearchReference);
    }

    /**
     * @param RechercheParcours $guidedSearchReference
     *
     * @return GuidedSearch
     */
    private function createNewOne(RechercheParcours $guidedSearchReference)
    {
        $guidedSearch = new GuidedSearch();
        $guidedSearch->setGuidedSearchReference($guidedSearchReference);

        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $guidedSearch->setOwner($this->tokenStorage->getToken()->getUser());
        }

        $this->entityManager->persist($guidedSearch);
        $this->entityManager->flush($guidedSearch);

        $this->session->set(self::SESSION_ID, $guidedSearch->getId());

        return $guidedSearch;
    }
}
