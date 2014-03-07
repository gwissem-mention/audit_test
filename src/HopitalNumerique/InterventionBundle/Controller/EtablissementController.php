<?php
/**
 * Contrôleur de gestion des établissement utilisés pour les interventions.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Contrôleur de gestion des établissement utilisées pour les interventions.
 */
class EtablissementController extends Controller
{
    /**
     * Action qui renvoie une liste d'établissements. Les paramètres passables sont :
     * <ul>
     *   <li>region : L'ID de la région des établissements</li>
     *   <li>departement : L'ID du département des établissements</li>
     * </ul>
     * 
     * @return \Symfony\Component\HttpFoundation\Response Un objet JSON comprenant la liste des établissements
     */
    public function jsonEtablissementsAction()
    {
        $etablissementsFiltres = array();
        if ($this->get('request')->query->has('region'))
            $etablissementsFiltres['region'] = intval($this->get('request')->query->get('region')); 
        if ($this->get('request')->query->has('departement'))
            $etablissementsFiltres['departement'] = intval($this->get('request')->query->get('departement')); 

        $etablissementsRegroupesParTypeOrganismeJson = $this->get('hopitalnumerique_intervention.manager.form_etablissement')->jsonEtablissementsRegroupesParTypeOrganisme($etablissementsFiltres);

        return new \Symfony\Component\HttpFoundation\Response(
            $etablissementsRegroupesParTypeOrganismeJson
        );
    }
}
