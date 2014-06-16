<?php

namespace HopitalNumerique\UserBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité RefusCandidature.
 */
class RefusCandidatureManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\UserBundle\Entity\RefusCandidature';
    
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
        $refusCandidature = array();
        
        $results = $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();
        
        foreach ($results as $key => $result)
        {
            $refusCandidature[ $result['id'] ] = $result;
            
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
            unset($refusCandidature[$result['id']]['userNom']);
            unset($refusCandidature[$result['id']]['userPrenom']);
            
            //Ajout de la colonne "Prenom NOM"
            $refusCandidature[ $result['id'] ]['nomPrenom'] = $prenom.' '.$nom;
        }
        
        return array_values($refusCandidature);
    }

    /**
     * Récupère tout les refus de candidature de tout les utilisateurs/questionnaire et le formate dans un tableau
     * 
     * @return array Tableau: key = idUser - valeur = Tableau: key = idQuestionnaire - valeur = Tableau: RefusCandidature
     */
    public function getRefusCandidatureByQuestionnaire()
    {
        //Tableau ordonné pour le resultat
        $resultat = array();
        
        //Récupèration de tous les refus
        $refusCandidatures = $this->findAll();
        
        //Gestion des refus
        foreach ($refusCandidatures as $refusCandidature)
        {            
            if(!array_key_exists($refusCandidature->getUser()->getId(), $resultat))
                $resultat[$refusCandidature->getUser()->getId()] = array();
            
            $resultat[$refusCandidature->getUser()->getId()][$refusCandidature->getQuestionnaire()->getId()] = $refusCandidature;            
        }
        
        return $resultat;
    }
        
    /**
     * Vérifie qu'un refus existe pour un utilisateur et un questionnaire donné
     * 
     * @param integer $idUser            Id de l'utilisateur
     * @param integer $idQuestionnaire   Id du questionnaire
     * @param array   $refusCandidatures Tableau des refus de candidature (temporaire, à mettre en cache !)
     * 
     * @return boolean Présence d'un refus
     */
    public function refusExisteByUserByQuestionnaire($idUser, $idQuestionnaire, $refusCandidatures)
    {
        return array_key_exists($idUser, $refusCandidatures) && array_key_exists($idQuestionnaire, $refusCandidatures[$idUser]);            
    }

}