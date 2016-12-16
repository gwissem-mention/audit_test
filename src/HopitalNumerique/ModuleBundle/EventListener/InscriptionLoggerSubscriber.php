<?php

namespace HopitalNumerique\ModuleBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use HopitalNumerique\CoreBundle\Service\Log;
use HopitalNumerique\ModuleBundle\Entity\Inscription;
use HopitalNumerique\ModuleBundle\Entity\Module;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

class InscriptionLoggerSubscriber implements EventSubscriber
{
    /** @var Log */
    protected $logger;

    /**
     * InterventionSubscriber constructor.
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
     * Log inscription on prePersist
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Inscription) {
            $this->log($entity, 'inscription');
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Inscription) {

            // Handle désinscription
            if ($args->hasChangedField('etatInscription')) {
                $validReferenceIds = [409];
                $oldValue = $args->getOldValue('etatInscription');
                $newValue = $args->getNewValue('etatInscription');

                $isValid = $newValue instanceof Reference
                    && in_array($newValue->getId(), $validReferenceIds)
                    && (
                        null === $oldValue
                        || (
                            $oldValue instanceof Reference
                            && !in_array($oldValue->getId(), $validReferenceIds)
                        )
                    )
                ;

                if ($isValid) {
                    $this->log($entity, 'desinscription');
                }
            }

            // Handle evaluation state changed
            if ($args->hasChangedField('etatEvaluation')) {
                $validReferenceIds = 29;
                $oldValue = $args->getOldValue('etatEvaluation');
                $newValue = $args->getNewValue('etatEvaluation');

                $isEvaluated = $newValue instanceof Reference
                    && $newValue->getId() === $validReferenceIds
                    && (
                        null === $oldValue
                        || (
                            $oldValue instanceof Reference
                            && $oldValue->getId() !== $validReferenceIds
                        )
                    )
                ;

                if ($isEvaluated) {
                    $this->log($entity, 'evaluate');
                }
            }
        }
    }

    protected function log(Inscription $inscription, $action)
    {
        $this->logger->Logger(
            $action,
            $inscription->getSession()->getModule(),
            $inscription->getSession()->getModule()->getTitre(),
            Module::class,
            $inscription->getUser()
        );
    }
}
