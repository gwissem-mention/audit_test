<?php

namespace HopitalNumerique\InterventionBundle\EventListener;

use HopitalNumerique\AutodiagBundle\Event\InterventionEvent;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Event\InterventionDemandeEvent;
use HopitalNumerique\InterventionBundle\Events;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class InterventionListener implements EventSubscriberInterface
{

    protected $container;
    /**
     * EntryListener constructor.
     * @param Doctrine $doctrine
     * @param Container $container
     * @param TokenStorage $security
     */
    public function __construct(Doctrine $doctrine, Container $container, TokenStorage $security)
    {
        $this->doctrine = $doctrine;
        $this->container = $container;
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::INTERVENTION_REQUEST => 'requestIntervention',
            Events::INTERVENTION_ACCEPT => 'acceptIntervention',
            Events::INTERVENTION_EVALUATION => 'evaluationIntervention',
            Events::INTERVENTION_EVALUATION_FRONT => 'evaluationInterventionFront',
        );
    }

    public function requestIntervention(InterventionDemandeEvent $intervention)
    {
        $action = 'request';
        $class = 'HopitalNumerique\InterventionBundle\Entity\InterventionDemande';
        $ambassador = $intervention->getInterventionDemande()->getAmbassadeur()->getNom() . ' ' . $intervention->getInterventionDemande()->getAmbassadeur()->getPrenom();


        $this->container->get('hopitalnumerique_core.log')->Logger(
            $action,
            $intervention->getInterventionDemande(),
            $ambassador,
            $class,
            $intervention->getInterventionDemande()->getReferent()
        );
    }

    public function acceptIntervention(InterventionDemandeEvent $intervention)
    {
        $action = 'accept';
        $class = 'HopitalNumerique\InterventionBundle\Entity\InterventionDemande';
        $ambassador = $intervention->getInterventionDemande()->getAmbassadeur()->getNom() . ' ' . $intervention->getInterventionDemande()->getAmbassadeur()->getPrenom();

        $this->container->get('hopitalnumerique_core.log')->Logger(
            $action,
            $intervention->getInterventionDemande(),
            $ambassador,
            $class,
            $intervention->getInterventionDemande()->getReferent()
        );
    }

    public function evaluationIntervention(InterventionDemandeEvent $intervention)
    {
        if (!empty($intervention->getInterventionDemande()->getEvaluationEtat()) && !empty($intervention->getOldInterventionDemande()->getEvaluationEtat())) {
            if ($intervention->getInterventionDemande()->getEvaluationEtat()->getId() != $intervention->getOldInterventionDemande()->getEvaluationEtat()->getId() && $intervention->getInterventionDemande()->getEvaluationEtat()->getId() == $this->container->getParameter('id_reference_evaluate')) {

                $action = 'evaluate';
                $class = 'HopitalNumerique\InterventionBundle\Entity\InterventionDemande';
                $ambassador = $intervention->getInterventionDemande()->getAmbassadeur()->getNom() . ' ' . $intervention->getInterventionDemande()->getAmbassadeur()->getPrenom();


                $this->container->get('hopitalnumerique_core.log')->Logger(
                    $action,
                    $intervention->getInterventionDemande(),
                    $ambassador,
                    $class,
                    $intervention->getInterventionDemande()->getReferent()
                );
            }
        }
    }

    public function evaluationInterventionFront(InterventionDemandeEvent $intervention)
    {

        $action = 'evaluate';
        $class = 'HopitalNumerique\InterventionBundle\Entity\InterventionDemande';
        $ambassador = $intervention->getInterventionDemande()->getAmbassadeur()->getNom() . ' ' . $intervention->getInterventionDemande()->getAmbassadeur()->getPrenom();


        $this->container->get('hopitalnumerique_core.log')->Logger(
            $action,
            $intervention->getInterventionDemande(),
            $ambassador,
            $class,
            $intervention->getInterventionDemande()->getReferent()
        );
    }
}
