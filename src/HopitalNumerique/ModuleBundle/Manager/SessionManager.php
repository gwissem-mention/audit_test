<?php

namespace HopitalNumerique\ModuleBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Session.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class SessionManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ModuleBundle\Entity\Session';

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid( $condition = null )
    {
        $sessions = $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();

        $result = array();

        foreach ($sessions as $key => $session) 
        {
            $nbInscritsAccepte   = 0;
            $nbInscritsEnAttente = 0;
            $nbPlacesRestantes   = $session->getNombrePlaceDisponible();

            foreach ($session->getInscriptions() as $inscription) 
            {
                if($inscription->getEtatInscription()->getId() === 406)
                    $nbInscritsEnAttente++;
                elseif($inscription->getEtatInscription()->getId() === 407)
                {
                    $nbInscritsAccepte++;
                    $nbPlacesRestantes--;
                }
            }

            $result[$key] = array(
                'id'                       => $session->getId(),
                'dateOuvertureInscription' => $session->getDateOuvertureInscription(),
                'dateFermetureInscription' => $session->getDateFermetureInscription(),
                'dateSession'              => $session->getDateSession(),
                'duree'                    => $session->getDuree()->getLibelle(),
                'horaires'                 => $session->getHoraires(),
                'nbInscrits'               => $nbInscritsAccepte,
                'nbInscritsEnAttente'      => $nbInscritsEnAttente,
                'placeRestantes'           => $nbPlacesRestantes . '/' . $session->getNombrePlaceDisponible(),
                'etat'                     => $session->getEtat()->getLibelle()
            );
        }

        return $result;
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getAllDatasForGrid( $condition = null )
    {
        $sessions = $this->getRepository()->getAllDatasForGrid( $condition )->getQuery()->getResult();

        $result = array();

        foreach ($sessions as $key => $session) 
        {
            $nbInscritsAccepte   = 0;
            $nbInscritsEnAttente = 0;
            $nbPlacesRestantes   = $session->getNombrePlaceDisponible();

            foreach ($session->getInscriptions() as $inscription) 
            {
                if($inscription->getEtatInscription()->getId() === 406)
                    $nbInscritsEnAttente++;
                elseif($inscription->getEtatInscription()->getId() === 407)
                {
                    $nbInscritsAccepte++;
                    $nbPlacesRestantes--;
                }
            }

            $result[$key] = array(
                'id'                       => $session->getId(),
                'moduleTitre'              => $session->getModule()->getTitre(),
                'dateOuvertureInscription' => $session->getDateOuvertureInscription(),
                'dateFermetureInscription' => $session->getDateFermetureInscription(),
                'dateSession'              => $session->getDateSession(),
                'duree'                    => $session->getDuree()->getLibelle(),
                'horaires'                 => $session->getHoraires(),
                'nbInscrits'               => $nbInscritsAccepte,
                'nbInscritsEnAttente'      => $nbInscritsEnAttente,
                'placeRestantes'           => $nbPlacesRestantes . '/' . $session->getNombrePlaceDisponible(),
                'etat'                     => $session->getEtat()->getLibelle()
            );
        }

        return $result;
    }

    /**
     * Retourne la liste des sessions du formateur
     *
     * @param User $user L'utilisateur concerné
     * 
     * @return array
     */
    public function getSessionsForFormateur( $user )
    {
        return $this->getRepository()->getSessionsForFormateur( $user )->getQuery()->getResult();
    }
}