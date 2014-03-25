<?php
/**
 * Manager pour les états des évaluations d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager;

use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluationEtat;

/**
 * Manager pour les états des évaluations d'intervention.
 */
class InterventionEvaluationEtatManager
{
    /**
     * @var \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager $referenceManager Manager de Reference
     */
    private $referenceManager;
    
    /**
     * Constructeur du manager gérant les états d'évaluation d'intervention.
     *
     * @param \HopitalNumerique\ReferenceBundle\Manager\ReferenceManager $referenceManager Manager de Reference
     * @return void
     */
    public function __construct(ReferenceManager $referenceManager)
    {
        $this->referenceManager = $referenceManager;
    }
    
    /**
     * Retourne l'état d'évaluation d'intervention correspondant à Attente.
     * 
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference Référence de l'état d'évaluation d'intervention correspondant à Attente
     */
    public function getInterventionEvaluationEtatAttente()
    {
        return $this->findOneById(InterventionEvaluationEtat::getInterventionEvaluationEtatAttenteId());
    }
    /**
     * Retourne l'état d'évaluation d'intervention correspondant à À évaluer.
     * 
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference Référence de l'état d'évaluation d'intervention correspondant à À évaluer
     */
    public function getInterventionEvaluationEtatAEvaluer()
    {
        return $this->findOneById(InterventionEvaluationEtat::getInterventionEvaluationEtatAEvaluerId());
    }
    /**
     * Retourne l'état d'évaluation d'intervention correspondant à Évalué.
     * 
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference Référence de l'état d'évaluation d'intervention correspondant à Évalué
     */
    public function getInterventionEvaluationEtatEvalue()
    {
        return $this->findOneById(InterventionEvaluationEtat::getInterventionEvaluationEtatEvalueId());
    }

    
    /**
     * Récupère un état d'évaluation d'intervention par rapport à son référence ID.
     *
     * @param integer $id ID de la référence correspondant à l'état d'évaluation d'intervention
     */
    private function findOneById($referenceid)
    {
        return $this->referenceManager->findOneById($referenceid);
    }
}
