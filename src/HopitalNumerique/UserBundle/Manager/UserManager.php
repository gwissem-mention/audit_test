<?php

namespace HopitalNumerique\UserBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;

class UserManager extends BaseManager
{
    protected $_class = '\HopitalNumerique\UserBundle\Entity\User';
    protected $_securityContext;
    protected $_managerReponse;
    protected $_managerRefusCandidature;    
    protected $_managerDomaine;    
    protected $_options;

    public function __construct($managerUser, $securityContext, $managerReponse, $managerRefusCandidature, $managerDomaine)
    {
        parent::__construct($managerUser);
        $this->_securityContext = $securityContext;
        //Récupération des managers Réponses et Questionnaire
        $this->_managerReponse          = $managerReponse;
        $this->_managerRefusCandidature = $managerRefusCandidature;
        $this->_managerDomaine          = $managerDomaine;
        $this->_options                 = array();
    }

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getDatasForGrid( \StdClass $condition = null )
    {
        $userConnected = $this->getUserConnected();
        $users = $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();
        $usersForGrid = array();

        $idExpert      = 1;
        $idAmbassadeur = 2;
        
        //Récupération des questionnaires et users
        $questionnaireByUser = $this->_managerReponse->reponseExiste($idExpert, $idAmbassadeur);
        
        $aujourdHui = new \DateTime('now');
        
        //On enlève un mois à la date courante pour prévenir 1mois à l'avance
        $interval    = new \DateInterval('P1M');
        $interval->m = -1;
        
        $refusCandidature = $this->_managerRefusCandidature->getRefusCandidatureByQuestionnaire();

        $domainesByUsers = $this->_managerDomaine->getDomainesByUsers();
        
        //Pour chaque utilisateur, set la contractualisation à jour
        foreach ($users as $key => $user)
        {    
            //Filtre uniquement si l'utilisateur connecté à un domaine, sinon on affiche toujours tout le monde
            if(!empty($userConnected->getDomainesId()))
            {
                //Si l'utilisateur n'a pas de domaine concerné, il n'apparait plus dans la liste
                if(!array_key_exists($user['id'], $domainesByUsers))
                {
                    continue;
                }
                //Sinon on vérifié que l'utilisateur courant et l'utilisateur connecté appartiennent au(x) même(s) domaine(s)
                else
                {
                    $notInArray = true;
                    $domainesIdUserConnected = $userConnected->getDomainesId();

                    foreach ($domainesIdUserConnected as $idDomaineUserConnected)
                    {
                        if(in_array($idDomaineUserConnected, $domainesByUsers[$user['id']]['id']))
                        {
                            $notInArray = false;
                            break;
                        }
                    }

                    if($notInArray)
                    {
                        continue;
                    }
                }
            }

            //Récupération des questionnaires rempli par l'utilisateur courant
            $questionnairesByUser = array_key_exists($user['id'], $questionnaireByUser) ? $questionnaireByUser[$user['id']] : array();
            
            //Récupèration d'un booléen : Vérification de réponses pour le questionnaire expert, que son role n'est pas expert et que sa candidature n'a pas encore été refusé
            $user['expert'] = (in_array($idExpert, $questionnairesByUser) 
                                        && !in_array('ROLE_EXPERT_6', $user["roles"]) 
                                        && !$this->_managerRefusCandidature->refusExisteByUserByQuestionnaire($user['id'], $idExpert, $refusCandidature));
            
            //Récupèration d'un booléen : Vérification de réponses pour le questionnaire expert, que son role n'est pas expert et que sa candidature n'a pas encore été refusé
            $user['ambassadeur'] = (in_array($idAmbassadeur, $questionnairesByUser) 
                                        && !in_array('ROLE_AMBASSADEUR_7', $user["roles"]) 
                                        && !$this->_managerRefusCandidature->refusExisteByUserByQuestionnaire($user['id'], $idAmbassadeur, $refusCandidature));
            
            $dateCourante = new \DateTime($user['contra']);
            $dateCourante->add($interval);
            $user['contra'] = ('' != $user['contra']) ? ($dateCourante >= $aujourdHui) : false;

            //Gestion du domaine
            $user['domaines'] = array_key_exists($user['id'], $domainesByUsers) ? $domainesByUsers[$user['id']]['url'] : '';

            $usersForGrid[] = $user;
        }
        
        return $usersForGrid;
    }

    /**
     * Override : Récupère les données Etablissement pour le grid sous forme de tableau
     *
     * @return array
     */
    public function getEtablissementForGrid( $condition = null )
    {
        return $this->getRepository()->getEtablissementForGrid( $condition )->getQuery()->getResult();
    }

    /**
     * Técupère les établissements pour l'export CSV
     *
     * @return array
     */
    public function getEtablissementForExport( $ids )
    {
        return $this->getRepository()->getEtablissementForExport( $ids )->getQuery()->getResult();
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
     * Retourne la liste des ambassadeurs de la région et du domaine
     *
     * @param Reference $region  La région filtrée
     * @param integer   $domaine Le domaine fonctionnel
     *
     * @return array
     */
    public function getAmbassadeursByRegionAndDomaine( $region, $domaine = null )
    {
        return $this->getRepository()->getAmbassadeursByRegionAndDomaine( $region, $domaine )->getQuery()->getResult();
    }

    /**
     * Retourne la liste des ambassadeurs de la région et de la publication
     *
     * @param Reference $region La région filtrée
     * @param Objet     $objet  La publication
     *
     * @return array
     */
    public function getAmbassadeursByRegionAndProduction( $region, $objet )
    {
        return $this->getRepository()->getAmbassadeursByRegionAndProduction( $region, $objet )->getQuery()->getResult();
    }

    /**
     * [getUsersGroupeEtablissement description]
     *
     * @param  array  $criteres [description]
     *
     * @return [type]
     */
    public function getUsersGroupeEtablissement($criteres = array())
    {
        return $this->getRepository()->getUsersGroupeEtablissement($criteres)->getQuery()->getResult();
    }

    /**
     * Retourne la liste des utilisateurs possédant le role demandé
     *
     * @param string $role Le rôle demandé
     *
     * @return array
     */
    public function findUsersByRole( $role )
    {
        return $this->getRepository()->findUsersByRole($role)->getQuery()->getResult();
    }

    /**
     * Retourne le premier utilisateur correspondant au role et à la région demandés
     *
     * @param string $role      Le rôle demandé
     * @param int    $idRegion  Region demandée
     *
     * @return array
     */
    public function findUsersByRoleAndRegion( $idregion, $role )
    {
        return $this->getRepository()->findUsersByRoleAndRegion($idregion, $role)->getQuery()->getOneOrNullResult();
    }

    /**
     * Retourne un unique CMSI.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     * @return \HopitalNumerique\UserBundle\Entity\User|null Un CMSI si trouvé, sinon NIL
     */
    public function getCmsi(array $criteres)
    {
        return $this->getRepository()->getCmsi($criteres);
    }
    /**
     * Retourne un unique directeur.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     * @return \HopitalNumerique\UserBundle\Entity\User|null Un directeur si trouvé, sinon NIL
     */
    public function getDirecteur(array $criteres)
    {
        return $this->getRepository()->getDirecteur($criteres);
    }
    /**
     * Retourne une liste d'ambassadeurs.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des ambassadeurs
     */
    public function getAmbassadeurs(array $criteres = array())
    {
        return $this->getRepository()->getAmbassadeurs($criteres);
    }
    /**
     * Retourne une liste d'utilisateurs ES ou Enregistré.
     *
     * @param array $criteres Filtres à appliquer sur la liste
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des utilisateurs
     */
    public function getESAndEnregistres(array $criteres = array())
    {
        return $this->getRepository()->getESAndEnregistres($criteres);
    }

    /**
     * Récupère les utilisateurs ayant répondues au questionnaire passé en paramètre
     *
     * @param  int idQuestionnaire Identifiant du questionnaire
     *
     * @return result
     */
    public function getUsersByQuestionnaire( $idQuestionnaire )
    {
        return $this->getRepository()->getUsersByQuestionnaire( $idQuestionnaire )->getQuery()->getResult();
    }

    public function getUserConnected()
    {
        return $this->_securityContext->getToken()->getUser();
    }

    /**
     * Récupère tous les utilisateurs (tous les rôles)
     *
     * @return \HopitalNumerique\UserBundle\Entity\User[] La liste des utilisateurs
     */
    public function getAllUsers() 
    {
        return $this->getRepository()->getAllUsers()->getQuery()->getResult();
    }

  /**
   * Récupère le nombre d'établissements connectés
   *
   * @return int
   */
  public function getNbEtablissements() {
    return count($this->getRepository()->getNbEtablissements()->getQuery()->getResult());
  }
}