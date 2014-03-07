<?php
/**
 * Contrôleur de gestion des utilisateurs utilisés pour les interventions.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Contrôleur de gestion des utilisateurs utilisés pour les interventions.
 */
class UserController extends Controller
{
    /**
     * Action qui renvoie une liste d'établissements. Les paramètres passables sont :
     * <ul>
     *   <li>etablissementRattachementSante : L'ID ou les IDs de l'établissement de l'utilisateur</li>
     * </ul>
     * 
     * @return \Symfony\Component\HttpFoundation\Response Un objet JSON comprenant la liste des utilisateurs
     */
    public function jsonUsersAction()
    {
        $usersFiltres = array();
        if ($this->get('request')->query->has('etablissementRattachementSante'))
            $usersFiltres['etablissementRattachementSante'] = intval($this->get('request')->query->get('etablissementRattachementSante'));

        $usersJson = $this->get('hopitalnumerique_intervention.manager.form_user')->jsonUsers($usersFiltres);

        return new \Symfony\Component\HttpFoundation\Response(
            $usersJson
        );
    }
}
