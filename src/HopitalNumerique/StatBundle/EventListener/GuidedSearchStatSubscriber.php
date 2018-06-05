<?php

namespace HopitalNumerique\StatBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcours;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursDetails;
use HopitalNumerique\RechercheParcoursBundle\Entity\RechercheParcoursGestion;
use HopitalNumerique\RechercheParcoursBundle\Repository\RechercheParcoursRepository;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\StatBundle\Service\GuidedSearchStatLogger;
use HopitalNumerique\UserBundle\Entity\User;
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Allows to watch guided search access (i.e. check access to each step of guided search) and to log statistics about it
 */
class GuidedSearchStatSubscriber implements EventSubscriberInterface
{
    /**
     * List of routes processed by current subscriber
     */
    const GUIDED_SEARCH_ROUTES = [
        'hopital_numerique_recherche_parcours_homepage_front',
        'hopital_numerique_guided_search_step',
    ];

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var CurrentDomaine
     */
    private $currentDomain;

    /**
     * @var RechercheParcoursRepository
     */
    private $pathRepository;

    /**
     * @var GuidedSearchStatLogger
     */
    private $statisticsLogger;

    /**
     * @param SessionInterface $session
     * @param TokenStorageInterface $tokenStorage
     * @param CurrentDomaine $currentDomain
     * @param RechercheParcoursRepository $pathRepository
     * @param GuidedSearchStatLogger $statisticsLogger
     */
    public function __construct(
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        CurrentDomaine $currentDomain,
        RechercheParcoursRepository $pathRepository,
        GuidedSearchStatLogger $statisticsLogger
    ) {
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->currentDomain = $currentDomain;
        $this->pathRepository = $pathRepository;
        $this->statisticsLogger = $statisticsLogger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return ['kernel.request' => 'onKernelRequest'];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $currentRoute = $event->getRequest()->get('_route');

        if (!in_array($currentRoute, self::GUIDED_SEARCH_ROUTES)) {
            return;
        }

        $params = $event->getRequest()->get('_route_params');

        $entryId = null;
        $pathId = null;
        $pathStepId = null;
        $pathSubStepId = null;
        $user = $this->tokenStorage->getToken()->getUser();

        if ('hopital_numerique_recherche_parcours_homepage_front' === $currentRoute) {
            $entryId = $params['id'];
        } elseif ('hopital_numerique_guided_search_step' === $currentRoute) {
            /** @var RechercheParcours $path */
            $path = $this->pathRepository->find($params['guidedSearchReference']);

            $pathId = $path->getId();
            $pathStepId = $params['parentReference'];

            $pathSubStepId = (null !== $params['subReference'])
                ? $params['subReference']
                : null
            ;

            $entryId = $path->getRecherchesParcoursGestion()->getId();
        }

        $this->statisticsLogger->logStat(
            $this->currentDomain->get(),
            $entryId,
            $pathId,
            $pathStepId,
            $pathSubStepId,
            $user instanceof User ? $user : null,
            $this->session->getId()
        );
    }
}
