<?php
namespace HopitalNumerique\ReferenceBundle\Doctrine\Glossaire;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;

/**
 * Lecture du glossaire.
 */
class Reader
{
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager ReferenceManager
     */
    private $referenceManager;


    /**
     * Constructeur.
     */
    public function __construct(ReferenceManager $referenceManager)
    {
        $this->referenceManager = $referenceManager;
    }


    /**
     * Retourne le glossaire selon un domaine.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine Domaine
     * @return array<\HopitalNumerique\ReferenceBundle\Entity\Reference> Glossaire
     */
    public function getGlossaireGroupedByLetterByDomaine(Domaine $domaine)
    {
        $glossaireReferences = $this->referenceManager->findByDomaines([$domaine], true, null, null, null, null, true);
        usort($glossaireReferences, array($this, 'order'));

        return $this->groupGlossaireByLetter($glossaireReferences);
    }

    /**
     * Retourne le glossaire trié par lettre.
     *
     * @param array<\HopitalNumerique\ReferenceBundle\Entity\Reference> $glossaireReferences Références
     * @return array Glossaire
     */
    private function groupGlossaireByLetter(array $glossaireReferences)
    {
        $glossaireByLetter = ['#' => []];
        foreach (range('a', 'z') as $letter) {
            $glossaireByLetter[$letter] = [];
        }

        foreach ($glossaireReferences as $glossaireReference) {
            $firstLetter = substr(strtolower($glossaireReference->getSigleForGlossaire()), 0, 1);
            if (!array_key_exists($firstLetter, $glossaireByLetter)) {
                $firstLetter = '#';
            }

            $glossaireByLetter[$firstLetter][] = $glossaireReference;
        }

        return $glossaireByLetter;
    }

    /**
     * Fonction callback pour trier les éléments du glossaire.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $glossaireReference1 Référence 1
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $glossaireReference2 Référence 2
     * @return integer Ordre
     */
    private function order(Reference $glossaireReference1, Reference $glossaireReference2)
    {
        return (strcmp($glossaireReference1->getSigleForGlossaire(), $glossaireReference2->getSigleForGlossaire()));
    }
}
