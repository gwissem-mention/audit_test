<?php
/**
 * Contrôleur des demandes d'intervention pour la console administrative.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller\Admin;

use HopitalNumerique\InterventionBundle\Entity\InterventionRegroupementType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\InterventionBundle\Entity\InterventionEtat;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Component\Form\Form;
use HopitalNumerique\InterventionBundle\Exception\InterventionException;
use Nodevo\RoleBundle\Entity\Role;
use Symfony\Component\HttpFoundation\Response;

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
        
        if ($utilisateurConnecte->hasRoleCmsi())
            return $this->render('HopitalNumeriqueInterventionBundle:Demande:Listes/cmsi.html.twig');
        else if ($utilisateurConnecte->hasRoleAmbassadeur())
            return $this->render('HopitalNumeriqueInterventionBundle:Demande:Listes/ambassadeur.html.twig');
        else return $this->render('HopitalNumeriqueInterventionBundle:Demande:Listes/etablissement.html.twig');
    }
}
