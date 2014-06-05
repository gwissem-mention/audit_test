<?php
/**
 * Contrôleur de gestion des objets utilisés pour les interventions.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Contrôleur de gestion des objets utilisées pour les interventions.
 */
class ObjetController extends Controller
{
    /**
     * Action qui renvoie une liste des objets. Les paramètres passables sont :
     * <ul>
     *   <li>ambassadeur : L'ID de l'ambassadeur des objets</li>
     * </ul>
     * 
     * @return \Symfony\Component\HttpFoundation\Response Un objet JSON comprenant la liste des objets
     */
    public function jsonObjetsByAmbassadeurAction(User $ambassadeur)
    {
        $objets = $this->get('hopitalnumerique_intervention.manager.form_objet')->jsonObjetsByAmbassadeur($ambassadeur);

        return new \Symfony\Component\HttpFoundation\Response($objets);
    }
}
