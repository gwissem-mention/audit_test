<?php
/**
 * Contrôleur des formulaires d'évaluation des demandes d'intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\InterventionBundle\Controller\Form;

use HopitalNumerique\InterventionBundle\Entity\InterventionEvaluation;

use HopitalNumerique\InterventionBundle\Entity\InterventionDemande;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Contrôleur des formulaires d'évaluation des demandes d'intervention.
 */
class EvaluationController extends Controller
{
    /**
     * Génération dynamique du questionnaire en chargeant les réponses de l'utilisateur passés en param, ajout d'une route de redirection quand tout s'est bien passé
     *
     * @param HopiUser          $user               Utilisateur courant
     * @param HopiQuestionnaire $questionnaire      Questionnaire à afficher
     * @param string            $themeQuestionnaire Theme de formulaire utilisé
     *
     * @return Ambigous <\HopitalNumerique\QuestionnaireBundle\Controller\Form, \Symfony\Component\HttpFoundation\RedirectResponse, \Symfony\Component\HttpFoundation\Response>
     */
    public function editAction(InterventionDemande $interventionDemande)
    {
        $utilisateurConnecte = $this->get('security.context')->getToken()->getUser();
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneById(InterventionEvaluation::getEvaluationQuestionnaireId());

        return $this->_renderForm(
            array(
                'interventionDemande' => $interventionDemande,
                'questionnaire' => $questionnaire
            ),
            'HopitalNumeriqueInterventionBundle:Evaluation/Form:edit.html.twig'
        );
    }

    /**
     * Effectue le render des formulaires de Questionnaire
     *
     * @param string $formName Nom du service associé au formulaire
     * @param array(Entity) $options Tableau d'entité necessaire à l'affichage
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function _renderForm($options, $view)
    {
        $questionnaire    = $options['questionnaire'];
        $interventionDemande = $options['interventionDemande'];
        $user = $interventionDemande->getReferent();
        $readOnly = (!$interventionDemande->evaluationEtatEstAEvaluer());
    
        //Création du formulaire via le service
        $form = $this->createForm('nodevo_questionnaire_questionnaire', $questionnaire, array(
            'label_attr' => array(
                'idUser'           => $user->getId(),
                'idQuestionnaire'  => $questionnaire->getId(),
                'readOnly'         => $readOnly,
                'interventionDemande' => $interventionDemande,
                'paramId' => $interventionDemande->getId()
            )
        ));
        $request = $this->get('request');

        if (!$readOnly && $request->isMethod('POST'))
        {
            $form->handleRequest($request);

            if ($form->isValid())
            {
                //Les champs file uploadés ne sont pas dans params, params ne recupère que les inputs
                $params = $request->get('nodevo_questionnaire_questionnaire');

                //Récupération des réponses pour le questionnaire et utilisateur courant, triées par idQuestion en clé
                $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser($questionnaire->getId(), $user->getId(), true, $interventionDemande->getId());

                //Gestion des réponses
                foreach ($params as $key => $param)
                {
                    //Récupération de l'id de la question, la clé est sous la forme : "type_id_alias"
                    $arrayParamKey = explode('_', $key);
                    
                    //Le tableau de arrayParamKey : 0 => type du champ - 1 => Id de la question - 2+=> alias du champ
                    $typeParam  = isset($arrayParamKey) && key_exists(0, $arrayParamKey)  ? $arrayParamKey[0] : '';
                    $idQuestion = isset($arrayParamKey) && key_exists(1, $arrayParamKey)  ? $arrayParamKey[1] : 0;
                    
                    // Ids des objets choisis
                    if ($key == 'interventionobjets_26_evaluation_productions')
                    {
                        $idQuestion = 26;
                        $param = implode(',', $param);
                    }
                    /*elseif ($key == 'intervention_objets_26_evaluation_productions')
                    {
                        $idQuestion = 26;
                        $param = implode(',', $param);
                    }*/

                    //Si l'id de la question n'a pas été récupéré alors on ne sauvegarde pas la question (exemple avec le cas particulier du token du formulaire)
                    if (0 === $idQuestion || '' === $idQuestion || '_token' === $key)
                        continue;
                    
                    $question = $this->get('hopitalnumerique_questionnaire.manager.question')->findOneBy(array('id' => $idQuestion));
    
                    //récupération de la réponse courante
                    $reponse = key_exists($idQuestion, $reponses) ? $reponses[$idQuestion] : null;
    
                    //Mode ajout
                    if (is_null($reponse))
                    {
                        $reponse = $this->get('hopitalnumerique_questionnaire.manager.reponse')->createEmpty();
                        $reponse->setUser($user);
                        $reponse->setQuestion($question);
                        $reponse->setParamId($interventionDemande->getId());
                        
                    }
                    //Mode ajout + édition : set la nouvelle réponse
                    $reponse->setReponse($param);
                    
                    if ('entity' === $typeParam)
                    {
                        $reponse->setReference($this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $param)));
                    }
    
                    //Test ajout ou edition
                    $new = is_null($reponse->getId()) ? true : false;
    
                    //Mise à jour de la réponse dans le tableau des réponses
                    $reponses[$idQuestion] = $reponse;
                }
    
                //Envoie du mail à l'utilisateur pour l'aleter de la validation de sa candidature
                
                
                $interventionDemande->setInterventionEtat($this->get('hopitalnumerique_intervention.manager.intervention_etat')->getInterventionEtatTermine());
                $interventionDemande->setEvaluationEtat($this->get('hopitalnumerique_intervention.manager.intervention_evaluation_etat')->getInterventionEvaluationEtatEvalue());
                $this->get('hopitalnumerique_intervention.manager.intervention_demande')->save($interventionDemande);
 
                $this->get('hopitalnumerique_intervention.manager.intervention_courriel')->envoiCourrielEvaluationRemplie($interventionDemande->getCmsi(), $interventionDemande->getAmbassadeur(), $this->generateUrl('hopital_numerique_intervention_evaluation_voir', array('interventionDemande' => $interventionDemande->getId()), true));
                
                $this->get('session')->getFlashBag()->add('success', 'Votre évaluation a été enregistrée, merci.');

                //Mise à jour/création des réponses
                $this->get('hopitalnumerique_questionnaire.manager.reponse')->save($reponses);

                return $this->redirect($this->generateUrl('hopital_numerique_intervention_demande_liste'));
            }
        }
    
        return $this->render( $view , array(
            'form'          => $form->createView(),
            'interventionDemande' => $interventionDemande,
            'questionnaire' => $questionnaire,
            'readOnly'          => $readOnly
        ));
    }
}
