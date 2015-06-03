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
     * @return array Liste des objets pour les listes de formulaire
     */
    public function getObjetsChoices()
    {
        return $this->objetManager->getObjetsByTypes( array(175, 176, 177, 178, 179, 180, 181, 182) );
    }
    /**
     * Retourne la liste des objets pour les listes de formulaire.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur L'ambassadeur pour le filtre
     * @return array Liste des objets pour les listes de formulaire
     */
    public function getConnaissancesChoices(User $ambassadeur = null)
    {
        if(is_null($ambassadeur))
        {
            return $this->referenceManager->findBy(array('code' => 'PERIMETRE_FONCTIONNEL_DOMAINES_FONCTIONNELS'), array('order' => 'ASC'));
        }
        else
        {
            $connaissances = array();
            foreach ($ambassadeur->getConnaissancesAmbassadeurs() as $connaissance) 
            {
                if(!is_null($connaissance->getConnaissance()))
                {
                    $connaissances[] = $connaissance->getDomaine();
                }
            }

            return $connaissances;
        }
    }
    /**
     * Retourne la liste des objets pour les listes de formulaire.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $ambassadeur L'ambassadeur pour le filtre
     * @return array Liste des objets pour les listes de formulaire
     */
    public function getConnaissancesSIChoices(User $ambassadeur = null)
    {
        if(is_null($ambassadeur))
        {
            return $this->referenceManager->findBy(array('code' => 'CONNAISSANCES_AMBASSADEUR_SI'), array('order' => 'ASC'));
        }
        else
        {
            $connaissancesSI = array();
            foreach ($ambassadeur->getConnaissancesAmbassadeursSI() as $connaissanceSI) 
            {
                if(!is_null($connaissanceSI->getConnaissance()))
                {
                    $connaissancesSI[] = $connaissanceSI->getDomaine();
                }
            }

            return $connaissancesSI;
        }
    }
}
