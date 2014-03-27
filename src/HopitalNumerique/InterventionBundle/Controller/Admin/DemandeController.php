<?php
/**
 * Contrôleur des demandes d'intervention pour la console administrative.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Contrôleur des demandes d'intervention.
 */
class DemandeController extends Controller
{
    /**
     * Action pour la visualisation d'une liste de demandes d'intervention.
     *
     * @return \Symfony\Component\HttpFoundation\Response La vue de la liste de demandes d'intervention
     */
    public function listeAction()
    {
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        
        return $this->render('HopitalNumeriqueInterventionBundle:Demande:Listes/etablissement.html.twig');
    }
}
