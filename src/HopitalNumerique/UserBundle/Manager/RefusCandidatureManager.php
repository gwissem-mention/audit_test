<?php

namespace HopitalNumerique\UserBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

/**
 * Manager de l'entité Contractualisation.
 */
class RefusCandidatureManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\UserBundle\Entity\RefusCandidature';
    
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