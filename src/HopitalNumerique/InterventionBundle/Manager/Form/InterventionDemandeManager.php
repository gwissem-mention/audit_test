<?php
/**
 * Manager pour le formulaire des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager\Form;

use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\ObjetBundle\Manager\ObjetManager;

/**
 * Manager pour le formulaire des demandes d'intervention.
 */
class InterventionDemandeManager
{
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager Manager de Reference
     */
    private $referenceManager;
    /**
     * @var \HopitalNumerique\ObjetBundle\Manager\ObjetManager Manager de Objet
     */
    private $objetManager;

    /**
     * Constructeur du manager gérant les formulaires de demandes d'intervention.
     *
     * @param \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager $referenceManager Manager de Reference
     * @param \HopitalNumerique\ObjetBundle\Manager\ObjetManager $objetManager Manager de Objet
     * @return void
     */
    public function __construct(ReferenceManager $referenceManager, ObjetManager $objetManager)
    {
        $this->referenceManager = $referenceManager;
        $this->objetManager = $objetManager;
    }

    /**
     * Retourne la liste des types d'intervention pour les listes de formulaire.
     *
     * @return array Liste des types d'intervention pour les listes de formulaire
     */
    public function getInterventionTypesChoices()
    {
        return $this->referenceManager->findBy(array('code' => 'TYPE_INTERVENTION'));
    }
    /**
     * Retourne la liste des états d'intervention pour les listes de formulaire.
     *
     * @return array Liste des états d'intervention pour les listes de formulaire
     */
    public function getInterventionEtatsChoices()
    {
        return $this->referenceManager->findBy(array('code' => 'ETAT_DEMANDE_INTERVENTION'));
    }
    /**
     * Retourne la liste des états des évaluations d'intervention pour les listes de formulaire.
     *
     * @return array Liste des états des évaluations d'intervention pour les listes de formulaire
     */
    public function getEvaluationEtatsChoices()
    {
        return $this->referenceManager->findBy(array('code' => 'ETAT_EVALUATION'));
    }
    /**
     * Retourne la liste des états des remboursements d'intervention pour les listes de formulaire.
     *
     * @return array Liste des états des remboursements d'intervention pour les listes de formulaire
     */
    public function getRemboursementEtatsChoices()
    {
        return $this->referenceManager->findBy(array('code' => 'ETAT_REMBOURSEMENT'));
    }
    /**
     * Retourne la liste des objets pour les listes de formulaire.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur L'ambassadeur pour le filtre
     * @return array Liste des objets pour les listes de formulaire
     */
    public function getObjetsChoices(User $ambassadeur)
    {
        return $this->objetManager->getObjetsByAmbassadeur($ambassadeur);
    }
}
