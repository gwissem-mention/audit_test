<?php

namespace HopitalNumerique\InterventionBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use HopitalNumerique\CoreBundle\Service\Log;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

class InterventionSubscriber implements EventSubscriber
{
    /** @var Log */
    protected $logger;

    /**
     * InterventionSubscriber constructor.
     *
     * @param Log $logger
     */
    public function __construct(Log $logger)
    {
        $this->logger = $logger;
    }

    public function getSubscribedEvents()
    {
        return [
            'prePersist',
            'preUpdate',
        ];
    }

    /**
     * Log intervention demand on prePersist.
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof InterventionDemande) {
            $this->log($entity, 'request');
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof InterventionDemande) {
            // Handle accepted state changed
            if ($args->hasChangedField('interventionEtat')) {
                $acceptedStateIds = [17, 21];
                $oldState = $args->getOldValue('interventionEtat');
                $newState = $args->getNewValue('interventionEtat');

                $isAccepted = $newState instanceof Reference
                    && in_array($newState->getId(), $acceptedStateIds)
                    && (
                        null === $oldState
                        || (
                            $oldState instanceof Reference
                            && !in_array($oldState->getId(), $acceptedStateIds)
                        )
                    )
                ;

                if ($isAccepted) {
                    $this->log($entity, 'accept');
                }
            }

            // Handle evaluation state changed
            if ($args->hasChangedField('evaluationEtat')) {
                $acceptedStateId = 29;
                $oldState = $args->getOldValue('evaluationEtat');
                $newState = $args->getNewValue('evaluationEtat');

                $isEvaluated = $newState instanceof Reference
                    && $newState->getId() === $acceptedStateId
                    && (
                        null === $oldState
                        || (
                            $oldState instanceof Reference
                            && $oldState->getId() !== $acceptedStateId
                        )
                    )
                ;

                if ($isEvaluated) {
                    $this->log($entity, 'evaluate');
                }
            }
        }
    }

    protected function log(InterventionDemande $intervention, $action)
    {
        $this->logger->Logger(
            $action,
            $intervention,
            sprintf('%s %s', $intervention->getAmbassadeur()->getLastname(), $intervention->getAmbassadeur()->getFirstname()),
            InterventionDemande::class,
            $intervention->getReferent()
        );
    }
}
