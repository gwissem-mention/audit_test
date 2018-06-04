<?php

namespace HopitalNumerique\ModuleBundle\Service\Widget\DataProvider;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ModuleBundle\Entity\SessionStatus;
use Nodevo\AclBundle\Manager\AclManager;
use Symfony\Component\Routing\RouterInterface;
use HopitalNumerique\ModuleBundle\Entity\Inscription;
use Symfony\Component\Translation\TranslatorInterface;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\QuestionnaireBundle\Entity\Reponse;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
use HopitalNumerique\ModuleBundle\Repository\InscriptionRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class RegistrationProvider
 */
class RegistrationProvider
{
    /**
     * @var InscriptionRepository
     */
    protected $registrationRepository;

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
     * RegistrationProvider constructor.
     *
     * @param InscriptionRepository $registrationRepository
     * @param TokenStorageInterface $tokenStorage
     * @param AclManager            $aclManager
     * @param RouterInterface       $router
     * @param TranslatorInterface   $translator
     */
    public function __construct(
        InscriptionRepository $registrationRepository,
        TokenStorageInterface $tokenStorage,
        AclManager $aclManager,
        RouterInterface $router,
        TranslatorInterface $translator
    ) {
        $this->registrationRepository = $registrationRepository;
        $this->tokenStorage = $tokenStorage;
        $this->aclManager = $aclManager;
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * Returns the formatted registrations for the session widget
     *
     * @param Domaine $domain
     *
     * @return array
     */
    public function getRegistrationData(Domaine $domain)
    {
        $registrations = $this->registrationRepository->getInscriptionsForUser(
            $this->tokenStorage->getToken()->getUser(),
            $domain
        );

        $data = [];

        foreach ($registrations as $registration) {
            $data[] = [
                'info' => $this->manageRegistrationInformation($registration),
                'actions' => $this->manageRegistrationActions($registration),
            ];
        }

        return $data;
    }

    /**
     * @param Inscription $registration
     *
     * @return array
     */
    protected function manageRegistrationInformation(Inscription $registration)
    {
        $information = [];

        $information['session_date'] = $registration->getSession()->getDateSessionString();
        $information['module_title'] = $registration->getSession()->getModule()->getTitre();

        if (null !== $registration->getEtatInscription()) {
            $information['registration_state'] = $registration->getEtatInscription()->getLibelle();
        }

        if ($this->aclManager->checkAuthorization(
            $this->router->generate('hopitalnumerique_paiement_front'),
            $this->tokenStorage->getToken()->getUser()
        )) {
            if (null !== $registration->getEtatRemboursement()) {
                $information['refund_state'] =
                    $registration->getEtatRemboursement()->getLibelle()
                    . ' (N.'
                    . $registration->getFacture()->getUser()->getId()
                    . $registration->getFacture()->getId()
                    . ')'
                ;
            }

            if (null !== $registration->getFacture()) {
                $bill_state = $registration->getFacture()->isPayee()
                    ? $this->translator->trans('session.bill.paid', [], 'widget')
                    : $this->translator->trans('session.bill.unpaid', [], 'widget')
                ;

                $information['bill_state'] =
                    $bill_state
                    . ' : '
                    . $registration->getFacture()->getTotal()
                    . $this->translator->trans('session.bill.currency', [], 'widget')
                ;
            }
        } else {
            $information['renfund_state'] = 'NA';
            $information['bill_state'] = 'NA';
        }

        return $information;
    }

    /**
     * @param Inscription $registration
     *
     * @return array
     */
    protected function manageRegistrationActions(Inscription $registration)
    {
        $actions = [];

        $actions['module'] = $this->router->generate(
            'hopitalnumerique_module_module_show_front',
            ['id' => $registration->getSession()->getModule()->getId()]
        );

        if (SessionStatus::STATUT_PARTICIPATION_OK_ID === $registration->getEtatParticipation()->getId()
            && $this->hadResponsesForSurvey($this->tokenStorage->getToken()->getUser()->getReponses())
            && Reference::TO_EVALUATE_ID === $registration->getEtatEvaluation()->getId()
        ) {
            $actions['evaluate'] = $this->router->generate(
                'hopitalnumerique_module_evaluation_form_front',
                ['id' => $registration->getSession()->getId()]
            );
        } elseif (Reference::EVALUATED_ID === $registration->getEtatEvaluation()->getId()) {
            $actions['show']     = $this->router->generate(
                'hopitalnumerique_module_evaluation_view_front',
                ['id' => $registration->getSession()->getId()]
            );
            $actions['download'] = $this->router->generate(
                'hopitalnumerique_module_inscription_attestation_front',
                ['id' => $registration->getId()]
            );
        }

        if (SessionStatus::STATUT_PARTICIPATION_OK_ID === $registration->getEtatParticipation()->getId()) {
            $actions['export']   = $this->router->generate(
                'hopitalnumerique_module_inscription_export_liste_front',
                ['id' => $registration->getId()]
            );
        } elseif (SessionStatus::STATUT_PARTICIPATION_WAITING_ID === $registration->getEtatParticipation()->getId()) {
            $actions['cancel']   = $this->router->generate(
                'hopitalnumerique_module_inscription_annulation_inscription_front',
                ['id' => $registration->getId(), 'json' => 'false']
            );
        }

        return $actions;
    }

    /**
     * @param $responses
     *
     * @return bool
     */
    protected function hadResponsesForSurvey($responses)
    {
        /** @var Reponse $response */
        foreach ($responses as $response) {
            if (Questionnaire::MODULE_EVALUATION_SURVEY_ID === $response->getQuestion()->getQuestionnaire()->getId()) {
                return true;
            }
        }

        return false;
    }
}
