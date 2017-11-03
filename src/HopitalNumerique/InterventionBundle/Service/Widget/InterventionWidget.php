<?php

namespace HopitalNumerique\InterventionBundle\Service\Widget;

use HopitalNumerique\NewAccountBundle\Model\Widget\WidgetExtension;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Routing\RouterInterface;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Symfony\Component\Translation\TranslatorInterface;
use HopitalNumerique\NewAccountBundle\Model\Widget\Widget;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\WidgetAbstract;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareTrait;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluationEtat;
use HopitalNumerique\NewAccountBundle\Service\Dashboard\DomainAwareInterface;
use HopitalNumerique\InterventionBundle\Repository\InterventionDemandeRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class InterventionWidget
 */
class InterventionWidget extends WidgetAbstract implements DomainAwareInterface
{
    use DomainAwareTrait;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var InterventionDemandeRepository
     */
    protected $interventionRepository;

    /**
     * InterventionWidget constructor.
     *
     * @param \Twig_Environment             $twig
     * @param TokenStorageInterface         $tokenStorage
     * @param TranslatorInterface           $translator
     * @param RouterInterface               $router
     * @param InterventionDemandeRepository $interventionRepository
     */
    public function __construct(
        \Twig_Environment $twig,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator,
        RouterInterface $router,
        InterventionDemandeRepository $interventionRepository
    ) {
        parent::__construct($twig, $tokenStorage, $translator);

        $this->router = $router;
        $this->interventionRepository = $interventionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidget()
    {
        $domainAllowed = false;

        foreach ($this->domains as $domain) {
            if (Domaine::DOMAINE_HOPITAL_NUMERIQUE_ID === $domain->getId()) {
                $domainAllowed = true;
                continue;
            }
        }

        if (!$domainAllowed) {
            return null;
        }

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $data = [
            'new' => [],
            'processed' => [],
        ];

        if ($user->hasRoleCmsi()) {
            $newRequests = $this->interventionRepository->getGridDonneesCmsiDemandesNouvelles($user);

            foreach ($newRequests as $request) {
                $row['information'] = [
                    'creationDate' => $request['dateCreation']->format('d/m/y'),
                    'user' => $request['referent_nom'],
                    'structure' => $request['referentEtablissementNom']
                        . ' - '
                        . $request['referentEtablissementFiness']
                        . ' - '
                        . $request['referentRegionLibelle']
                    ,
                    'ambassador' => $request['ambassadeurInformations'],
                    'state' => $request['interventionEtatLibelle'],
                    'endDate' => $request['dateCreation']->add(
                        new \DateInterval('P' . InterventionEtat::$VALIDATION_CMSI_NOMBRE_JOURS . 'D')
                    )->format('d/m/y'),

                ];

                $row['actions'] = [
                    'show' => $this->router->generate(
                        'hopital_numerique_intervention_demande_voir',
                        ['id' => $request['id']]
                    ),
                    'edit' => $this->router->generate(
                        'hopital_numerique_intervention_demande_edit',
                        ['id' => $request['id']]
                    ),
                ];

                $data['new'][] = $row;
            }

            $processedRequests = $this->interventionRepository->getGridDonneesCmsiDemandesTraitees($user);

            foreach ($processedRequests as $request) {
                $row['information'] = [
                    'creationDate' => $request['dateCreation']->format('d/m/y'),
                    'user' => $request['referent_nom'],
                    'structure' => $request['referentEtablissementNom']
                        . ' - '
                        . $request['referentEtablissementFiness']
                        . ' - '
                        . $request['referentRegionLibelle']
                    ,
                    'ambassador' => $request['ambassadeurInformations'],
                    'state' => $request['interventionEtatLibelle'],
                ];

                if (null !== $request['cmsiDateChoix']) {
                    $row['information']['cmsiDate'] = 'CMSI : ' . $request['cmsiDateChoix']->format('d/m/y');
                }

                if (null !== $request['ambassadeurDateChoix']) {
                    $row['information']['ambassadorDate'] =
                        $this->translator->trans('intervention.network_professionnal', [], 'widget')
                        . ' '
                        . $request['ambassadeurDateChoix']->format('d/m/y')
                    ;
                }

                if (null !== $request['evaluationDate']) {
                    $row['information']['evaluationDate'] =
                        $this->translator->trans('intervention.evaluation', [], 'widget')
                        . ' : '
                        . $request['evaluationDate']->format('d/m/y')
                    ;
                }

                $row['actions'] = [
                    'show' => $this->router->generate(
                        'hopital_numerique_intervention_demande_voir',
                        ['id' => $request['id']]
                    ),
                ];

                if ($request['evaluationEtatId']
                    === InterventionEvaluationEtat::getInterventionEvaluationEtatAEvaluerId()
                ) {
                    if ($request['referentId'] === $user->getId()) {
                        $row['actions']['evaluate'] = $this->router->generate(
                            'hopital_numerique_intervention_evaluation_nouveau',
                            ['interventionDemande' => $request['id']]
                        );
                    } else {
                        $row['actions']['waiting'] = $this->translator->trans(
                            'intervention.action.waiting',
                            [],
                            'widget'
                        );
                    }
                } elseif ($request['evaluationEtatId']
                          === InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId()
                ) {
                    $row['actions']['evaluation'] =  $this->router->generate(
                        'hopital_numerique_intervention_evaluation_voir',
                        ['interventionDemande' => $request['id']]
                    );
                }

                $data['processed'][] = $row;
            }
        } elseif ($user->hasRoleAmbassadeur()) {
            $requests = $this->interventionRepository->getGridDonneesAmbassadeurDemandes($user);

            foreach ($requests as $request) {
                $row['information'] = [
                    'creationDate' => $request['dateCreation']->format('d/m/y'),
                    'user' => $request['referent_nom'],
                    'structure' => $request['referentEtablissementNom']
                        . ' - '
                        . $request['referentEtablissementFiness']
                        . ' - '
                        . $request['referentRegionLibelle']
                    ,
                    'state' => $request['interventionEtatLibelle'],
                ];

                if (null !== $request['cmsiDateChoix']) {
                    $row['information']['cmsiDate'] = 'CMSI : ' . $request['cmsiDateChoix']->format('d/m/y');
                }

                if (null !== $request['ambassadeurDateChoix']) {
                    $row['information']['ambassadorDate'] =
                        $this->translator->trans('intervention.network_professionnal', [], 'widget')
                        . ' '
                        . $request['ambassadeurDateChoix']->format('d/m/y')
                    ;
                }

                if (null !== $request['evaluationDate']) {
                    $row['information']['evaluationDate'] =
                        $this->translator->trans('intervention.evaluation', [], 'widget')
                        . ' : '
                        . $request['evaluationDate']->format('d/m/y')
                    ;
                }

                $row['actions'] = [
                    'show' => $this->router->generate(
                        'hopital_numerique_intervention_demande_voir',
                        ['id' => $request['id']]
                    ),
                ];

                if ($request['evaluationEtatId']
                    === InterventionEvaluationEtat::getInterventionEvaluationEtatAEvaluerId()
                ) {
                    $row['actions']['relaunch'] = $this->router->generate(
                        'hopital_numerique_intervention_evaluation_relaunch',
                        ['interventionDemande' => $request['id']]
                    );
                } elseif ($request['evaluationEtatId']
                          === InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId()
                ) {
                    $row['actions']['evaluation'] =  $this->router->generate(
                        'hopital_numerique_intervention_evaluation_voir',
                        ['interventionDemande' => $request['id']]
                    );
                }

                $data['processed'][] = $row;
            }
        } else {
            $requests = $this->interventionRepository->getGridDonneesEtablissementDemandes($user);

            foreach ($requests as $request) {
                $row['information'] = [
                    'creationDate' => $request['dateCreation']->format('d/m/y'),
                    'user' => $request['ambassadeurInformations'],
                    'state' => $request['interventionEtatLibelle'],
                ];

                if (null !== $request['cmsiDateChoix']) {
                    $row['information']['cmsiDate'] = 'CMSI : ' . $request['cmsiDateChoix']->format('d/m/y');
                }

                if (null !== $request['evaluationDate']) {
                    $row['information']['evaluationDate'] =
                        $this->translator->trans('intervention.evaluation', [], 'widget')
                        . ' : '
                        . $request['evaluationDate']->format('d/m/y')
                    ;
                }

                if (null !== $request['ambassadeurDateChoix']) {
                    $row['information']['ambassadorDate'] =
                        $this->translator->trans('intervention.network_professionnal', [], 'widget')
                        . ' '
                        . $request['ambassadeurDateChoix']->format('d/m/y')
                    ;
                }

                $row['actions'] = [
                    'show' => $this->router->generate(
                        'hopital_numerique_intervention_demande_voir',
                        ['id' => $request['id']]
                    )
                ];


                if ($request['evaluationEtatId'] === InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId()) {
                    $row['actions']['evaluation'] = $this->router->generate(
                        'hopital_numerique_intervention_evaluation_voir',
                        ['interventionDemande' => $request['id']]
                    );
                }

                $data['processed'][] = $row;
            }
        }

        if (empty($data['new']) && empty($data['processed'])) {
            return null;
        }

        $html = $this->twig->render('HopitalNumeriqueInterventionBundle:widget:intervention.html.twig', [
            'data' => $data,
        ]);

        $title = $this->translator->trans('intervention.title', [], 'widget');

        $widget = new Widget('interventions', $title, $html);
        $widget->addExtension(new WidgetExtension('count', $this->twig->render(
            '@NewAccount/widget/extension/badge_number_extension.html.twig',
            ['number' => count($data['new']) + count($data['processed'])]
        )));

        return $widget;
    }
}
