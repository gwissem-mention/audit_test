<?php

namespace HopitalNumerique\ModuleBundle\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\ModuleBundle\Entity\Session;

class EvaluationFrontController extends Controller
{
    /**
     * Affichage du formulaire d'évaluation
     */
    public function formulaireAction( Session $session )
    {
        return $this->renderForm( $session );
    }

    /**
     * Affichage du formulaire d'évaluation en readonly
     */
    public function formulaireVisualisationAction( Session $session )
    {
        return $this->renderForm( $session, true );
    }

    private function renderForm ( Session $session, $readOnly = false)
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();
        
        //Récupération du questionnaire
        $idQuestionnaireModuleEvaluation = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('module-evaluation');
        $questionnaire                   = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy( array('id' => $idQuestionnaireModuleEvaluation) );
        
        //Vérification si l'utilisateur connecté a participé à cette session, sinon il n'a pas accès au formulaire d'évaluation
        $aParticipe = false;
        $inscriptionsAcceptes = $session->getInscriptionsAccepte();
        foreach ($inscriptionsAcceptes as $inscriptionAccepte) 
        {
            if($user->getId() === $inscriptionAccepte->getUser()->getId()
                && $inscriptionAccepte->getEtatParticipation()->getId() === 411)
            {
                $aParticipe = true;
                break;
            }
        }

        if(!$aParticipe)
        {
            $this->get('session')->getFlashBag()->add( 'danger' , 'Vous n\'avez pas accès à cette session.' );

            return $this->redirect($this->generateUrl( 'hopitalnumerique_module_module_front' ));
        }

        //Création du formulaire via le service
        $form = $this->createForm( 'nodevo_questionnaire_questionnaire', $questionnaire, array(
                'label_attr' => array(
                    'idUser'           => $user->getId(),
                    'idQuestionnaire'  => $questionnaire->getId(),
                    'paramId'          => $session->getId(),
                    'readOnly'         => $readOnly 
                )
        ));

        $request = $this->get('request');

        if ( $form->handleRequest($request)->isValid() ) 
        {
            //Les champs file uploadés ne sont pas dans params, params ne recupère que les inputs
            $params = $request->get('nodevo_questionnaire_questionnaire');
            
            //Récupération des réponses pour le questionnaire et utilisateur courant, triées par idQuestion en clé pour la session courante
            $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser( $questionnaire->getId(), $user->getId(), true, $session->getId() );
            
            //Gestion des réponses
            foreach ($params as $key => $param)
            {
                //Récupération de l'id de la question, la clé est sous la forme : "type_id_alias"
                $arrayParamKey = explode('_', $key);

                //Le tableau de arrayParamKey : 0 => type du champ - 1 => Id de la question - 2+=> alias du champ
                $typeParam  = isset($arrayParamKey) && key_exists(0, $arrayParamKey)  ? $arrayParamKey[0] : '';
                $idQuestion = isset($arrayParamKey) && key_exists(1, $arrayParamKey)  ? $arrayParamKey[1] : 0;

                //Si l'id de la question n'a pas été récupéré alors on ne sauvegarde pas la question (exemple avec le cas particulier du token du formulaire)
                if(0 === $idQuestion || '' === $idQuestion || '_token' === $key)
                    continue;

                //récupération de la réponse courante
                $reponse = key_exists($idQuestion, $reponses) ? $reponses[$idQuestion] : null;    

                //Mode ajout
                if(is_null($reponse))
                {
                    $reponse = $this->get('hopitalnumerique_questionnaire.manager.reponse')->createEmpty();
                    $reponse->setUser($user);
                    $reponse->setQuestion($this->get('hopitalnumerique_questionnaire.manager.question')->findOneBy(array('id' => $idQuestion)));
                    $reponse->setParamId( $session->getId() );
                }
                //Mode ajout + édition : set la nouvelle réponse
                $reponse->setReponse($param);
                if('entity' === $typeParam)
                {
                    $reponse->setReference($this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $param)));
                }

                //Test ajout ou edition
                $new = is_null($reponse->getId());
                
                //Mise à jour de la réponse dans le tableau des réponses
                $reponses[$idQuestion] = $reponse;
            }

            //Mise à jour de la production du module dans la liste des productions maitrisées : uniquement pour les ambassadeurs
            if($this->container->get('security.context')->isGranted('ROLE_AMBASSADEUR_7'))
            {
                //Récupération des formations
                $formations = $session->getModule()->getProductions();
                
                //Pour chaque production on ajout l'utilisateur à la liste des ambassadeurs qui la maitrise
                foreach($formations as $formation)
                {
                    //Récupération des ambassadeurs pour vérifier si l'utilisateur actuel ne maitrise pas déjà cette formation
                    $ambassadeursFormation = $formation->getAmbassadeurs();
                    $ambassadeurIds = array();

                    foreach ($ambassadeursFormation as $ambassadeur)
                    {
                        $ambassadeurIds[] = $ambassadeur->getId();
                    }

                    if(!in_array($user->getId(), $ambassadeurIds))
                    {
                        $formation->addAmbassadeur( $user );
                        $this->get('hopitalnumerique_objet.manager.objet')->save( $formation );
                    }
                }
            }

            //Modifications de l'inscription: modification du statut "etatEvaluer"  
            $inscription = $this->get('hopitalnumerique_module.manager.inscription')->findOneBy( array('user' => $user, 'session' => $session) );
            $inscription->setEtatEvaluation( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => 29)));
            $this->get('hopitalnumerique_module.manager.inscription')->save( $inscription );

            $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Votre évaluation de la session ' . $session->getModule()->getTitre() . ' a bien été prise en compte, nous vous remercions.' );
                            
            //Mise à jour/création des réponses
            $this->get('hopitalnumerique_questionnaire.manager.reponse')->save( $reponses );

            return $this->redirect($this->generateUrl( 'hopitalnumerique_module_module_front' ));
        }
    
        return $this->render( 'HopitalNumeriqueModuleBundle:Front/Evaluation:form.html.twig' , array(
                'form'              => $form->createView(),
                'questionnaire'     => $questionnaire,
                'user'              => $user,
                'session'           => $session,
                'moduleSelectionne' => $session->getModule()
        ));
    }
}