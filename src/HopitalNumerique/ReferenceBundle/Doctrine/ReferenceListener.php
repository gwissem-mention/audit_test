<?php
namespace HopitalNumerique\ReferenceBundle\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

/**
 * Listener Doctrine pour Reference.
 */
class ReferenceListener
{
    /**
     * Événement prePersist.
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        if ($args->getEntity() instanceof Reference) {
            $this->preUpdate($args);
        }
    }

    /**
     * Événement preUpdate.
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        if ($args->getEntity() instanceof Reference) {
            $this->processDomaineAdding($args->getEntity());
        }
    }

    /**
     * Si la référence est lié à un parent dont aucun domaine n'est lié à ladite référence, on lui ajoute automatiquement les domaines du parent.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference Référence en cours d'enregistrement
     */
    private function processDomaineAdding(Reference $reference)
    {
        foreach ($reference->getParents() as $parent) {
            if (!$parent->hasAtLeastOneDomaine($reference->getDomaines())) {
                $reference->addDomaines($parent->getDomaines());
            }
        }
    }
}
