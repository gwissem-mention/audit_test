<?php
/**
 * Manager pour le formulaire des initiateur de demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Manager\Form;

/**
 * Manager pour le formulaire des initiateur de demandes d'intervention.
 */
class InterventionInitiateurManager
{
    /**
     * @var \HopitalNumerique\InterventionBundle\Manager\InterventionInitiateurManager Manager de Reference
     */
    private $interventionInitiateurManager;

    /**
     * Constructeur du manager gérant les formulaires de demandes d'intervention pour les initiateurs d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Manager\InterventionInitiateurManager $interventionInitiateurManager Manager de InterventionInitiateur
     * @return void
     */
    public function __construct(\HopitalNumerique\InterventionBundle\Manager\InterventionInitiateurManager $interventionInitiateurManager)
    {
        $this->interventionInitiateurManager = $interventionInitiateurManager;
    }

    /**
     * Retourne la liste des civilités pour les listes de formulaire.
     *
     * @return array Liste des civilités pour les listes de formulaire
     */
    public function getInterventionInitiateursChoices()
    {
        return $this->interventionInitiateurManager->findAll();
    }
}
