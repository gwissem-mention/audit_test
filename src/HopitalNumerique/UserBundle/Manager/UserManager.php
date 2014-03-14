<?php

namespace HopitalNumerique\UserBundle\Manager;

use Nodevo\AdminBundle\Manager\Manager as BaseManager;
use Symfony\Component\Security\Core\User\UserInterface;
use HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager;

class UserManager extends BaseManager
{
    protected $_class = '\HopitalNumerique\UserBundle\Entity\User';
    protected $_managerReponse;
    protected $_options;

    public function __construct($managerUser, $managerReponse, $managerQuestionnaire)
    {
        parent::__construct($managerUser);
        //Récupération des managers Réponses et Questionnaire
        $this->_managerReponse       = $managerReponse;
        $this->_managerQuestionnaire = $managerQuestionnaire;
        $this->_options              = array();
    }
    
    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( $condition = null )
    {
        $users = $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();

        $idExpert      = $this->_managerQuestionnaire->getQuestionnaireId('expert');
        $idAmbassadeur = $this->_managerQuestionnaire->getQuestionnaireId('ambassadeur');
        
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
            $users[$key]['contra'] = ('' != $user['contra']) ? ($dateCourante >= $aujourdHui) : false;            
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

    /**
     * Modifie l'état de tous les users
     *
     * @param array     $users Liste des utilisateurs
     * @param Reference $ref   RefStatut à mettre
     *
     * @return empty
     */
    public function toogleState( $users, $ref )
    {
        foreach($users as $user) {
            $user->setEtat( $ref );
            $user->setEnabled( ($ref->getId() == 3 ? 1 : 0) );
            $this->_em->persist( $user );
        }

        //save
        $this->_em->flush();
    }

    /**
     * On cherche a savoir si un user existe avec le role et la région de l'user modifié
     *
     * @param User $user L'utilisateur modifié
     *
     * @return boolean
     */
    public function userExistForRoleArs( $user )
    {
        return $this->getRepository()->userExistForRoleArs( $user )->getQuery()->getOneOrNullResult();
    }

    /**
     * On cherche a savoir si un user existe avec le role et la région de l'user modifié
     *
     * @param User $user L'utilisateur modifié
     *
     * @return boolean
     */
    public function userExistForRoleDirection( $user )
    {
        return $this->getRepository()->userExistForRoleDirection( $user )->getQuery()->getOneOrNullResult();
    }

    /**
     * Retourne la liste des ambassadeurs de la région $region
     *
     * @param Reference $region La région filtrée
     *
     * @return array
     */
    public function getAmbassadeursByRegion( $region )
    {
        return $this->getRepository()->getAmbassadeursByRegion( $region )->getQuery()->getResult();
    }
}