<?php
/**
 * Contrôleur de gestion des utilisateurs utilisés pour les interventions.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;

/**
 * Contrôleur de gestion des utilisateurs utilisés pour les interventions.
 */
class UserController extends Controller
{
    /**
     * Action qui renvoie une liste d'utilisateurs. Les paramètres passables sont :
     * <ul>
     *   <li>etablissementRattachementSante : L'ID ou les IDs de l'établissement de l'utilisateur</li>
     * </ul>
     * 
     * @return \Symfony\Component\HttpFoundation\Response Un objet JSON comprenant la liste des utilisateurs
     */
    public function jsonUsersAction()
    {
        $usersFiltres = array('enabled' => true);
        if ($this->get('request')->query->has('etablissementRattachementSante'))
            $usersFiltres['etablissementRattachementSante'] = $this->get('request')->query->get('etablissementRattachementSante');

        $usersJson = $this->get('hopitalnumerique_intervention.manager.form_user')->jsonUsers($usersFiltres);

        return new Response($usersJson);
    }
    /**
     * Action qui renvoie une liste de référents. Les paramètres passables sont :
     * <ul>
     *   <li>etablissementRattachementSante : L'ID ou les IDs de l'établissement de l'utilisateur</li>
     * </ul>
     * 
     * @return \Symfony\Component\HttpFoundation\Response Un objet JSON comprenant la liste des utilisateurs
     */
    public function jsonReferentsAction()
    {
        $usersFiltres = array();
        if ($this->get('request')->query->has('etablissementRattachementSante'))
            $usersFiltres['etablissementRattachementSante'] = $this->get('request')->query->get('etablissementRattachementSante');

        $usersJson = $this->get('hopitalnumerique_intervention.manager.form_user')->jsonReferents($usersFiltres);

        return new Response($usersJson);
    }
    /**
     * Action qui renvoie une liste d'ambassadeurs. Les paramètres passables sont :
     * <ul>
     *   <li>region : L'ID de la région de l'ambassadeur</li>
     * </ul>
     *
     * @return \Symfony\Component\HttpFoundation\Response Un objet JSON comprenant la liste des utilisateurs
     */
    public function jsonAmbassadeursAction(Request $request)
    {
        $usersFiltres = array();
        if ($request->query->has('region'))
            $usersFiltres['region'] = $request->query->get('region');
    
        $usersJson = $this->get('hopitalnumerique_intervention.manager.form_user')->jsonAmbassadeurs($usersFiltres);
    
        return new Response($usersJson);
    }
    
    /**
     * Action qui change l'ambassadeur d'une demandes d'intervention.
     *
     * @param \HopitalNumerique\InterventionBundle\Entity\InterventionDemande $interventionDemande La demande d'intervention qui change d'ambassadeur
     * @param \HopitalNumerique\UserBundle\Entity\User $nouvelAmbassadeur Le nouvelle ambassadeur de la demande
     * @return \Symfony\Component\HttpFoundation\Response 1 ssi le nouvel ambassadeur est valide
     */
    public function ajaxAmbassadeurChangeAction(InterventionDemande $interventionDemande, User $nouvelAmbassadeur)
    {
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        if ($utilisateurConnecte->hasRoleAmbassadeur() && $utilisateurConnecte->getId() == $interventionDemande->getAmbassadeur()->getId())
        {
            if ($this->get('hopitalnumerique_intervention.manager.intervention_demande')->changeAmbassadeur($interventionDemande, $nouvelAmbassadeur))
            {
                $this->get('session')->getFlashBag()->add('success', 'L\'ambassadeur a été modifié.');
                return new Response('1');
            }
        }
    
        return new Response('0');
    }
}
