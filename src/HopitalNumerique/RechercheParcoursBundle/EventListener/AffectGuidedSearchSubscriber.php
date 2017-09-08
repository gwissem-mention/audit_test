<?php

namespace HopitalNumerique\RechercheParcoursBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\Repository\GuidedSearchRepository;
use HopitalNumerique\RechercheParcoursBundle\Service\GuidedSearchRetriever;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class AffectGuidedSearchSubscriber implements EventSubscriberInterface
{
    /**
     * @var SessionInterface $session
     */
    protected $session;

    /**
     * @var GuidedSearchRepository $guidedSearchRepository
     */
    protected $guidedSearchRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * AffectGuidedSearchSubscriber constructor.
     *
     * @param SessionInterface $session
     * @param GuidedSearchRepository $guidedSearchRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(SessionInterface $session, GuidedSearchRepository $guidedSearchRepository, EntityManagerInterface $entityManager)
    {
        $this->session = $session;
        $this->guidedSearchRepository = $guidedSearchRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => [
                ['affectGuidedSearchToAuthenticatedUser', 0],
            ],
        ];
    }

    /**
     * Work if only logged user are able to share.
     * Otherwise, we should save risk analysis ids in session to identify witch one belongs to current logging in user
     *
     * @param AuthenticationEvent $event
     */
    public function affectGuidedSearchToAuthenticatedUser(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (
            !is_null($guidedSearchId = $this->session->get(GuidedSearchRetriever::SESSION_ID)) &&
            !is_null($guidedSearch = $this->guidedSearchRepository->find($guidedSearchId))
        ) {
            /** @var GuidedSearch $guidedSearch */
            $guidedSearch->setOwner($user);
            foreach ($guidedSearch->getSteps() as $step) {
                foreach ($step->getRisksAnalysis() as $analyse) {
                    $analyse->setOwner($user);
                }
            }

            $this->entityManager->flush();
        }
    }

}
