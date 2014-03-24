<?php

namespace HopitalNumerique\UserBundle\Util;

class GestionAffichageOnglets
{
	private $_managerReponse;
	private $_managerQuestionnaire;

	function __construct( $managerReponse, $managerQuestionnaire )
	{
		$this->_managerReponse = $managerReponse;
		$this->_managerQuestionnaire = $managerQuestionnaire;
	}

	/**
     * Fonction permettant d'envoyer un tableau d'option à la vue pour vérifier le role de l'utilisateur
     *
     * @param User $user
     * @return array
     */
    public function getOptions( $user )
    {
        $options = array(
                'ambassadeur' => false,
                'expert'      => false
        );

        //Récupération du questionnaire de l'expert
        $idQuestionnaireExpert = $this->_managerQuestionnaire->getQuestionnaireId('expert');
        //Récupération du questionnaire de l'ambassadeur
        $idQuestionnaireAmbassadeur = $this->_managerQuestionnaire->getQuestionnaireId('ambassadeur');
        
        //Récupération des réponses du questionnaire expert de l'utilisateur courant
        $reponsesExpert      = $this->_managerReponse->reponsesByQuestionnaireByUser($idQuestionnaireExpert, $user->getId());
        //Récupération des réponses du questionnaire ambassadeur de l'utilisateur courant
        $reponsesAmbassadeur = $this->_managerReponse->reponsesByQuestionnaireByUser($idQuestionnaireAmbassadeur, $user->getId());

        //Si il y a des réponses correspondant au questionnaire du groupe alors on donne l'accès
        $options['expert_form']      = !empty($reponsesExpert);
        $options['ambassadeur_form'] = !empty($reponsesAmbassadeur);
        
        //Dans tout les cas si l'utilisateur a le bon groupe on lui donne l'accès
        if( $user->hasRole('ROLE_EXPERT_6') )
            $options['expert'] = true;

        if( $user->hasRole('ROLE_AMBASSADEUR_7') )
            $options['ambassadeur'] = true;
    
        return $options;
    }
}