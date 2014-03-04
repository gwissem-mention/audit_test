<?php
/**
 * Contrôleur des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Contrôleur des demandes d'intervention.
 */
class DemandeController extends Controller
{
    /**
     * Action pour la création d'une nouvelle demande d'intervention.
     */
    public function nouveauAction()
    {
        return $this->render('HopitalNumeriqueInterventionBundle:Demande:nouveau.html.twig');
    }
}
