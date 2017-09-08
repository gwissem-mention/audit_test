<?php

namespace HopitalNumerique\InterventionBundle\Service\Widget;

use HopitalNumerique\InterventionBundle\Repository\InterventionDemandeRepository;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Class RequestWidget
 */
class RequestWidget extends WidgetAbstract
{
    /**
     * @var InterventionDemandeRepository $interventionRequestRepository
     */
    protected $interventionRequestRepository;

    /**
     * @param InterventionDemandeRepository $interventionRequestRepository
     */
    public function setInterventionRequestRepository(InterventionDemandeRepository $interventionRequestRepository)
    {
        $this->interventionRequestRepository = $interventionRequestRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user->hasRoleAmbassadeur()) {
            $stats = $this->interventionRequestRepository->getStatsForAmbassador($user);

            $html = $this->twig->render('HopitalNumeriqueInterventionBundle:widget:ambassador_request.html.twig', [
                'stats' => $stats,
            ]);
        } elseif ($user->hasRoleCmsi()) {
            $stats = $this->interventionRequestRepository->getStatsForCMSI($user);

            $html = $this->twig->render('HopitalNumeriqueInterventionBundle:widget:cmsi_request.html.twig', [
                'stats' => $stats,
            ]);
        } else {
            $interventions = $this->interventionRequestRepository->getOpenRequestForReferent($user);

            $html = $this->twig->render('HopitalNumeriqueInterventionBundle:widget:user_request.html.twig', [
                'interventions' => $interventions,
            ]);
        }

        $title = $this->translator->trans('request.title', [], 'widget');

        return new Widget('intervention-request', $title, $html);
    }
}
