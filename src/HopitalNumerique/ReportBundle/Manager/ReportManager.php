<?php

namespace HopitalNumerique\ReportBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Report.
 */
class ReportManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ReportBundle\Entity\Report';

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     * 
     * @author Alexis MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $reports = array();
        
        $results = $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();
        
        foreach ($results as $key => $result)
        {
            $reports[ $result['id'] ] = $result;
            
            // ----Traitement pour transformer le prénom "Jean-luc robert" en "Jean-Luc Robert"
            //Récupération du prénom
            $prenom = strtolower($result['userPrenom']);
            //Découpage du prénom sur le tiret
            $tempsPrenom = explode('-', $prenom);
            //Unsset de la variable
            $prenom = "";
            //Pour chaque bout on met une MAJ sur la première lettre de chaque mot, si il y en plusieurs c'est qu'il y avait un -
            foreach ($tempsPrenom as $key => $tempPrenom)
            {
                $prenom .= ("" !== $prenom) ? ('-' . ucwords($tempPrenom)) : ucwords($tempPrenom);
            }
            
            // ----Mise en majuscule du nom
            $nom = strtoupper($result['userNom']);

            //Suppression du nom et prenom
            unset($reports[$result['id']]['userNom']);
            unset($reports[$result['id']]['userPrenom']);
            
            //Ajout de la colonne "Prenom NOM"
            $reports[ $result['id'] ]['nomPrenom'] = $prenom.' '.$nom;

            $reports[ $result['id'] ]['date'] = $reports[ $result['id'] ]['date']->format('Y-m-d H:i:s');
        }
        
        return array_values($reports);
    }
}