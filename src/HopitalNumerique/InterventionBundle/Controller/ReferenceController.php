<?php
/**
 * Contrôleur de gestion des "références" utilisées pour les interventions.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Contrôleur de gestion des "références" utilisées pour les interventions.
 */
class ReferenceController extends Controller
{
    /**
     * Action qui renvoie une liste de départements. Les paramètres passables sont :
     * <ul>
     *   <li>region : L'ID de la région des départements à récupérer</li>
     * </ul>
     * 
     * @return \Symfony\Component\HttpFoundation\Response Un objet JSON comprenant la liste des départements
     */
    public function jsonDepartementsAction()
    {
        $departementsFiltres = array('code' => 'DEPARTEMENT');
        if ($this->get('request')->query->has('region'))
            $departementsFiltres['parent'] = intval($this->get('request')->query->get('region')); 
        
        $departements = $this->get('hopitalnumerique_reference.manager.reference')->findBy($departementsFiltres, array('libelle' => 'ASC'));
        $departementsListeFormatee = array();
        foreach ($departements as $departement)
        {
            $departementsListeFormatee[] = array(
                'id' => $departement->getId(),
                'libelle' => $departement->getLibelle()
            );
        }

        return new \Symfony\Component\HttpFoundation\Response(
            json_encode($departementsListeFormatee)
        );
    }
}
