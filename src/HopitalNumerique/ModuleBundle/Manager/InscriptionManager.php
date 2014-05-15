<?php

namespace HopitalNumerique\ModuleBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Inscription.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class InscriptionManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ModuleBundle\Entity\Inscription';

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
        $inscriptions = array();
        
        $results = $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();
        
        foreach ($results as $key => $result)
        {
            $inscriptions[ $result['id'] ] = $result;
            
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
            unset($inscriptions[$result['id']]['userNom']);
            unset($inscriptions[$result['id']]['userPrenom']);
            
            //Ajout de la colonne "Prenom NOM"
            $inscriptions[ $result['id'] ]['nomPrenom'] = $prenom.' '.$nom;
        }
        
        return array_values($inscriptions);
    }
    
    /**
     * Modifie l'état de toutes les inscriptions
     *
     * @param array     $inscriptions Liste des inscriptions
     * @param Reference $ref          RefStatut à mettre
     *
     * @return empty
     */
    public function toogleEtatInscription( $inscriptions, $ref )
    {
        foreach($inscriptions as $inscription) {
            $inscription->setEtatInscription( $ref );
            $this->_em->persist( $inscription );
        }
    
        //save
        $this->_em->flush();
    }
    
    /**
     * Modifie l'état de toutes les participations
     *
     * @param array     $inscriptions Liste des inscriptions
     * @param Reference $ref          RefStatut à mettre
     *
     * @return empty
     */
    public function toogleEtatParticipation( $inscriptions, $ref )
    {
        foreach($inscriptions as $inscription) {
            $inscription->setEtatParticipation( $ref );
            $this->_em->persist( $inscription );
        }
    
        //save
        $this->_em->flush();
    }
    
    /**
     * Modifie l'état de toutes les évaluations
     *
     * @param array     $inscriptions Liste des inscriptions
     * @param Reference $ref          RefStatut à mettre
     *
     * @return empty
     */
    public function toogleEtatEvaluation( $inscriptions, $ref )
    {
        foreach($inscriptions as $inscription) {
            $inscription->setEtatEvaluation( $ref );
            $this->_em->persist( $inscription );
        }
    
        //save
        $this->_em->flush();
    }

    /**
     * Retourne la liste des inscriptions de l'utilisateur pour la création des factures
     *
     * @param User $user L'utilisateur concerné
     *
     * @return array
     */
    public function getForFactures( $user )
    {
        return $this->getRepository()->getForFactures( $user )->getQuery()->getResult();
    }

    /**
     * Retourne la liste des inscriptions de l'utilisateur
     *
     * @param User $user L'utilisateur concerné
     * 
     * @return array
     */
    public function getInscriptionsForUser( $user )
    {
        return $this->getRepository()->getInscriptionsForUser( $user )->getQuery()->getResult();
    }
}