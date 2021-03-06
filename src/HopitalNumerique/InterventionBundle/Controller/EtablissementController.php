<?php

/**
 * Contrôleur de gestion des établissements utilisés pour les interventions.
 *
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur de gestion des établissements utilisées pour les interventions.
 */
class EtablissementController extends Controller
{
    /**
     * Action qui renvoie une liste d'établissements. Les paramètres passables sont :
     * <ul>
     *   <li>region : L'ID de la région des établissements</li>
     *   <li>departement : L'ID du département des établissements</li>
     * </ul>.
     *
     * @return Response Un objet JSON comprenant la liste des établissements
     */
    public function jsonEtablissementsAction()
    {
        $etablissementsFiltres = [];
        if ($this->get('request')->query->has('region')) {
            $etablissementsFiltres['region'] = intval($this->get('request')->query->get('region'));
        }

        if ($this->get('request')->query->has('departement')) {
            $etablissementsFiltres['departement'] = intval($this->get('request')->query->get('departement'));
        }

        $etablissements = $this->get('hopitalnumerique_intervention.manager.form_etablissement')->jsonEtablissements(
            $etablissementsFiltres
        );

        return new Response($etablissements);
    }
}
