<?php
namespace HopitalNumerique\ReferenceBundle\Doctrine\Reference;

use Doctrine\Common\Collections\Collection;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

/**
 * Service gérant la màj des domaines d'une reférence.
 */
class DomaineUpdater
{
    /**
     * @var array<\HopitalNumerique\DomaineBundle\Entity\Domaine> Domaines avant enregistrement
     */
    private $initialDomaines = null;

    /**
     * @var array<\HopitalNumerique\ReferenceBundle\Entity\Reference> Références parentes avant enregistrement
     */
    private $initialParents = null;


    /**
     * Spécifie la référence avant modification utilisateur.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference Référence
     */
    public function setInitialReference(Reference $reference)
    {
        $this->setInitialDomaines($reference->getDomaines());
        $this->setInitialParents($reference->getParents());
    }

    /**
     * Spécifie les domaines initiaux.
     *
     * @param array<\HopitalNumerique\DomaineBundle\Entity\Domaine> $domaines Domaines avant enregistrement
     */
    private function setInitialDomaines(Collection $domaines)
    {
        $this->initialDomaines = clone $domaines;
    }

    /**
     * Spécifie les références parentes initiales.
     *
     * @param array<\HopitalNumerique\ReferenceBundle\Entity\Reference> Références parentes avant enregistrement
     */
    private function setInitialParents(Collection $parents)
    {
        $this->initialParents = clone $parents;
    }


    /**
     * Màj les domaines de la référence.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference Référence
     */
    public function updateDomaines(Reference &$reference)
    {
        if (!$reference->isAllDomaines()) {
            foreach ($this->getNewParents($reference) as $newParent) {
                foreach ($newParent->getDomaines() as $parentDomaine) {
                    if (!$reference->hasDomaine($parentDomaine) && !$this->domaineHasBeenRemoved($reference, $parentDomaine)) {
                        $reference->addDomaine($parentDomaine);
                    }
                }
            }
        }
    }


    /**
     * Retourne les parents qui viennent d'être ajouté.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference Référence
     * @return array<\HopitalNumerique\ReferenceBundle\Entity\Reference> Parents juste ajoutés
     */
    private function getNewParents(Reference $reference)
    {
        $newParents = [];

        foreach ($reference->getParents() as $parent) {
            if (!$this->parentInitiallyPresent($parent)) {
                $newParents[] = $parent;
            }
        }

        return $newParents;
    }

    /**
     * Retourne si le parent était initialement présent.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference Référence
     * @return boolean Si était présent
     */
    private function parentInitiallyPresent(Reference $parent)
    {
        foreach ($this->initialParents as $initialParent) {
            if ($initialParent->equals($parent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne si le domaine a été supprimé par l'utilisateur.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference Référence
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine     $domaine   Domaine
     * @return boolean Si a été supprimé
     */
    private function domaineHasBeenRemoved(Reference $reference, Domaine $domaine)
    {
        return ($this->domaineInitiallyPresent($domaine) && !$reference->hasDomaine($domaine));
    }

    /**
     * Retourne si le domaine était initialement présent.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @return boolean Si était présent
     */
    private function domaineInitiallyPresent(Domaine $domaine)
    {
        foreach ($this->initialDomaines as $initialDomaine) {
            if ($initialDomaine->equals($domaine)) {
                return true;
            }
        }

        return false;
    }
}
