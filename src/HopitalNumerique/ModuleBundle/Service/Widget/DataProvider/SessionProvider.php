<?php

namespace HopitalNumerique\ModuleBundle\Service\Widget\DataProvider;

use Nodevo\AclBundle\Manager\AclManager;
use Symfony\Component\Routing\RouterInterface;
use HopitalNumerique\ModuleBundle\Entity\Session;
use Symfony\Component\Translation\TranslatorInterface;
use HopitalNumerique\ModuleBundle\Repository\SessionRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class SessionProvider
 */
class SessionProvider
{
    /**
     * @var SessionRepository
     */
    protected $sessionRepository;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AclManager
     */
    protected $aclManager;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * SessionProvider constructor.
     *
     * @param SessionRepository     $sessionRepository
     * @param TokenStorageInterface $tokenStorage
     * @param AclManager            $aclManager
     * @param RouterInterface       $router
     * @param TranslatorInterface   $translator
     */
    public function __construct(
        SessionRepository $sessionRepository,
        TokenStorageInterface $tokenStorage,
        AclManager $aclManager,
        RouterInterface $router,
        TranslatorInterface $translator
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->tokenStorage = $tokenStorage;
        $this->aclManager = $aclManager;
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * Returns the formatted sessions for the session widget
     *
     * @return array
     */
    public function getSessionData()
    {
        $sessions = $this->sessionRepository->getSessionsForFormateur(
            $this->tokenStorage->getToken()->getUser()
        );

        $data = [];

        foreach ($sessions as $session) {
            $data[] = [
                'info' => $this->manageSessionInformation($session),
                'actions' => $this->manageSessionActions($session),
            ];
        }

        return $data;
    }

    /**
     * @param Session $session
     *
     * @return array
     */
    protected function manageSessionInformation(Session $session)
    {
        return [
            'date_session' => $session->getDateSessionString(),
            'module_title' => $session->getModule()->getTitre(),
            'remaining_places' => count($session->getInscriptionsAccepte()) . '/' . $session->getNombrePlaceDisponible()
        ];
    }

    /**
     * @param Session $session
     *
     * @return array
     */
    protected function manageSessionActions(Session $session)
    {
        $actions = [
            'module' => $this->router->generate(
                'hopitalnumerique_module_module_show_front',
                ['id' => $session->getModule()->getId()]
            ),
            'description' => $this->router->generate(
                'hopitalnumerique_module_session_description_front',
                ['id' => $session->getId()]
            ),
            'participations' => $this->router->generate(
                'hopitalnumerique_module_inscription_particiption',
                ['id' => $session->getId()]
            ),
            'registrations' => $this->router->generate(
                'hopitalnumerique_module_session_formateur_session_front',
                ['id' => $session->getId()]
            ),
            'sheet' => $this->router->generate(
                'hopitalnumerique_module_session_impression_fiche',
                ['id' => $session->getId()]
            )
        ];

        if ($session->getDateSession() <= new \DateTime()) {
            $actions['evaluations'] = $this->router->generate(
                'hopitalnumerique_module_session_evaluation_front',
                ['id' => $session->getId()]
            );
        }

        return $actions;
    }
}
