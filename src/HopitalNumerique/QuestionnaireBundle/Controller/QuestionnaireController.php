<?php

namespace HopitalNumerique\QuestionnaireBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\UserBundle\Entity\User as HopiUser;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire as HopiQuestionnaire;
use HopitalNumerique\QuestionnaireBundle\Manager\QuestionnaireManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\VarDateTimeType;

/**
 * Controller des Questionnaire
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class QuestionnaireController extends Controller
{

    /**
     * Tableau de la route de redirection sous la forme :
     * array(
     *   'sauvegarde' => array( 'route' => nom_de_ma_route, 'arguments' => array ('keyArgument' => valueArgument))
     *   'quit'       => array( 'route' => nom_de_ma_route, 'arguments' => array ('keyArgument' => valueArgument))
     *  )
     *  
     * @var array
     */
    private $routeRedirection = array();
    
    /**
     * Génération dynamique du questionnaire en chargeant les réponses de l'utilisateur passés en param, ajout d'une route de redirection quand tout s'est bien passé
     * 
     * @param HopiUser $user Utilisateur courant
     * @param HopiQuestionnaire $questionnaire Questionnaire à afficher
     * @param json $routeRedirection Tableau de la route de redirection une fois que le formulaire est validé
     * 
     * @return Ambigous <\HopitalNumerique\QuestionnaireBundle\Controller\Form, \Symfony\Component\HttpFoundation\RedirectResponse, \Symfony\Component\HttpFoundation\Response>
     */
    public function editAction( HopiUser $user, HopiQuestionnaire $questionnaire, $routeRedirection = '')
    {      
        //Si le tableau n'est pas vide on le récupère
        if(!is_null($routeRedirection))
            $this->routeRedirection = $routeRedirection;
        
        return $this->_renderForm('nodevo_questionnaire_questionnaire',
                array(
                        'questionnaire'    => $questionnaire,
                        'user'             => $user
                ) ,
                'HopitalNumeriqueQuestionnaireBundle:Questionnaire:edit.html.twig'
        );
    }

    public function deleteAllAction()
    {
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
    private function _renderForm( $formName, $options, $view )
    {
        $user             = $options['user'];
        $questionnaire    = $options['questionnaire'];
    
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $questionnaire, array(
                'label_attr' => array(
                        'idUser'           => $user->getId(),
                        'idQuestionnaire'  => $questionnaire->getId(),
                        'routeRedirection' => $this->routeRedirection 
                )
        ));
        
        $request = $this->get('request');
    
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
    
            // On bind les données du form
            $form->handleRequest($request);
            
            $routeRedirection = json_decode($form["routeRedirect"]->getData(), true);
            
            //si le formulaire est valide
            if ($form->isValid()) {
    
                //Les champs file uploadés ne sont pas dans params, params ne recupère que les inputs
                $params = $request->get('nodevo_questionnaire_questionnaire');
                
                //Récupèrations des questions de type files pour le questionnaire courant
                $questionsFiles = $this->get('hopitalnumerique_questionnaire.manager.question')->getQuestionsByType( $questionnaire->getId() , 'file' );
                
                //Gestion des files uploadés
                $dossierRoot = $this->get('hopitalnumerique_questionnaire.manager.question')->getUploadRootDir($questionnaire->getNomMinifie());
                $files = array();
                
                //get All References, and convert to ArrayCollection
                $reponses = new ArrayCollection( $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUserByFileQuestion( $questionnaire->getId(), $user->getId(), false ) );
                
                //Parcourt les questions de champ file
                foreach ($questionsFiles as $key => $questionFiles)
                {   
                    //Récupère la réponse de la question courante
                    $criteria = Criteria::create()->where(Criteria::expr()->eq("question", $questionFiles) );
                    //Récupération d'un tableau comportant une seule réponse
                    $tempReponse = $reponses->matching( $criteria );
                    
                    // -v-v-v- GME 26/02/2014 : Traitement brouillon, le array_shift ou reset ne fonctionne pas -v-v-v-
                    $test = array();                    
                    foreach ($tempReponse as $temp)
                    {
                        $test[] = $temp;
                        break;
                    }                    
                    $reponse  = !empty($test) ? $test[0] : null;
                    // -^-^-^- Traitement brouillon, le array_shift ou reset ne fonctionne pas -^-^-^-
                
                    //Si il n'y a pas de réponses pour cette question pour cet utilisateur
                    if(is_null($reponse))
                    {
                        $reponse = $this->get('hopitalnumerique_questionnaire.manager.reponse')->createEmpty();
                        $reponse->setUser($user);
                        $reponse->setQuestion($questionFiles);
                    }
                
                    //Format du champ file
                    $champFile = $questionFiles->getTypeQuestion()->getLibelle() . '_' . $questionFiles->getId() . '_' . $questionFiles->getAlias();                    
                    $files[$questionFiles->getAlias()] = array(
                            'nom'  => $questionnaire->getNomMinifie() . '_' . $user->getId() . '_' . $user->getNom() . '_' . $user->getPrenom() . '_' . $questionFiles->getAlias() . '.pdf',
                            'file' => $form[$champFile]->getData(),
                            'reponse' => $reponse
                    );
                    
                    //MAJ/ajout du nouveau path
                    $files[$questionFiles->getAlias()]['reponse']->setReponse($files[$questionFiles->getAlias()]['nom']);
                } 
                
                $reponses = array();
                           
                //Parcourt les fichiers uploadés
                foreach ($files as $file)
                {
                    //Si le JS est désactivé, il se peut qu'il n'y ait pas de fichier uploadé
                    if(is_null($file['file']))
                        break;
                    
                    $file['file']->move($dossierRoot, $file['nom']);
        
                    $reponses[] = $file['reponse'];
                }
                    
                //Mise à jour / créations des réponses correspondantent aux fichiers
                $this->get('hopitalnumerique_questionnaire.manager.reponse')->save( $reponses );
                
                //Récupération des réponses pour le questionnaire et utilisateur courant, triées par idQuestion en clé
                $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser( $questionnaire->getId(), $user->getId(), true );
                
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
                    }
                    //Mode ajout + édition : set la nouvelle réponse
                    $reponse->setReponse($param);
                    if('entity' === $typeParam)
                    {
                        $reponse->setReference($this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $param)));
                    }
    
                    //Test ajout ou edition
                    $new = is_null($reponse->getId()) ? true : false;
                    
                    //Mise à jour de la réponse dans le tableau des réponses
                    $reponses[$idQuestion] = $reponse;
                }
                
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Candidature ' . $questionnaire->getNomMinifie() . ' ' . ($new ? 'créée.' : 'mise à jour.') );
                
                //Mise à jour/création des réponses
                $this->get('hopitalnumerique_questionnaire.manager.reponse')->save( $reponses );
    
                //Sauvegarde / Sauvegarde + quitte
                $do = $request->request->get('do');
                return $this->redirect( $do == 'save-close' ? $this->generateUrl($routeRedirection['quit']['route'], $routeRedirection['quit']['arguments']) : $this->generateUrl($routeRedirection['sauvegarde']['route'], $routeRedirection['sauvegarde']['arguments']));
            }
        }
    
        return $this->render( $view , array(
                'form'          => $form->createView(),
                'questionnaire' => $questionnaire,
                'user'          => $user
        ));
    }
    
}
