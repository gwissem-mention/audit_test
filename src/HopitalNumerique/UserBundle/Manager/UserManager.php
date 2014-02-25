<?php

namespace HopitalNumerique\UserBundle\Manager;

use Nodevo\UserBundle\Manager\UserManager as BaseManager;
use Symfony\Component\Security\Core\User\UserInterface;
use HopitalNumerique;

class UserManager extends BaseManager
{
    protected $_class = '\HopitalNumerique\UserBundle\Entity\User';
    protected $_managerReponse;

    public function __construct($managerUser, $managerReponse)
    {
        parent::__construct($managerUser);
        //Récupération des managers Réponses et Questionnaire
        $this->_managerReponse = $managerReponse;
    }
    
    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( $condition = null )
    {
        $users = $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();
        
        $idExpert      = HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager::_getQuestionnaireId('expert');
        $idAmbassadeur = HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager::_getQuestionnaireId('ambassadeur');
        
        //Récupération des questionnaires et users
        $questionnaireByUser = $this->_managerReponse->reponseExiste($idExpert, $idAmbassadeur);        
        
        $aujourdHui = new \DateTime('now');
        
        //On enlève un mois à la date courante pour prévenir 1mois à l'avance
        $interval    = new \DateInterval('P1M');
        $interval->m = -1;
        
        //Pour chaque utilisateur, set la contractualisation à jour
        foreach ($users as $key => $user)
        {              
            //Récupération des questionnaires rempli par l'utilisateur courant
            $questionnairesByUser = key_exists($user['id'], $questionnaireByUser) ? $questionnaireByUser[$user['id']] : array();
            
            //Vérification de réponses pour le questionnaire expert
            $users[$key]['expert'] = in_array($idExpert, $questionnairesByUser);
            
            //Vérification de réponses pour le questionnaire ambassadeur
            $users[$key]['ambassadeur'] = in_array($idAmbassadeur, $questionnairesByUser);           
            
            $dateCourante = new \DateTime($user['contra']);
            $dateCourante->add($interval);
            $users[$key]['contra'] = ('' != $user['contra']) ? ($dateCourante >= $aujourdHui ? true : false) : false;            
        }
        
        return $users;
    }

    /**
     * Retourne la liste des établissements autres
     *
     * @param stdClass $condition condition
     *
     * @return array
     */
    public function getDatasForGridEtablissement( $condition = null )
    {
        return $this->getRepository()->getDatasForGridEtablissement()->getQuery()->getResult();
    }
}