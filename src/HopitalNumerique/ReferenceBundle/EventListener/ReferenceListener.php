<?php

namespace HopitalNumerique\ReferenceBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\StatBundle\Entity\StatRecherche;

class ReferenceListener
{
    /**
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Reference) {
            return;
        }

        /** @var Reference $reference */
        $reference = $entity;

        $toBeRemoved = $args->getEntityManager()->getRepository('HopitalNumeriqueStatBundle:StatRecherche')
            ->findSearchHistoryByReferences($reference)
        ;

        /** @var StatRecherche $statRecherche */
        foreach ($toBeRemoved as $statRecherche) {
            $referenceIds = json_decode($statRecherche->getRequete());

            if (($key = array_search($reference->getId(), $referenceIds)) !== false) {
                unset($referenceIds[$key]);
            }

            $referenceIds = json_encode($referenceIds);

            $statRecherche->setRequete($referenceIds);
        }
    }
}
