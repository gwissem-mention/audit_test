<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\AutodiagBundle\Entity\Resultat;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nodevo\ToolsBundle\Tools\Chaine;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Front controller.
 */
class FrontController extends Controller
{
    /**
     * Liste les outils d'autodiagnostic présent dans les articles de la plateforme
     */
    public function indexAction()
    {
        $categ  = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'id' => 179 ) );
        $objets = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsByTypes( $categ );

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:index.html.twig' , array(
            'objets' => $objets
        ));
    }
    
    public function outilResultatAction( Outil $outil, $sansGabarit = false, Resultat $resultat )
    {
        if( $resultat->getStatut()->getId() == 419 )
        {
            return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_comptehn' ) );
        }
        return $this->outilAction($outil, $sansGabarit, $resultat);
    }

    /**
     * Affiche le Front vu chapitre
     *
     * @param Outil $outil L'entitée Outil
     * @ParamConverter("resultat", options = { "mapping": { "resultat": "id" } } )
     */
    public function outilAction( Outil $outil, $sansGabarit = false, Resultat $resultat = null )
    {
        //init some vars
        $chapitres      = $outil->getChapitres();
        $parents        = array();
        $enfants        = array();
        
        //build chapitres
        foreach($chapitres as $chapitre)
        {
            //Si on ne doit pas afficher le chapitre, on ne le prend pas en compte
            if( is_null($chapitre->getParent()) )
            {
                $parents[ $chapitre->getId() ]['parent'] = $chapitre;
                $parents[ $chapitre->getId() ]['childs'] = array();
            }
            else
            {
                $enfants[] = $chapitre;
            }
        }

        //reformate les chapitres
        foreach($enfants as $enfant) 
        {
            $parentId = $enfant->getParent()->getId();
            $parents[ $parentId ]['childs'][$enfant->getOrder()] = $enfant;
        }

        //reorder parents
        $chapitresOrdered = array();
        foreach($parents as $one)
        {
            $tmp = $one['parent'];

            //sort childs
            ksort($one['childs']);

            $chapitresOrdered[ $tmp->getOrder() ] = $one;
        }
        ksort($chapitresOrdered);

        //get Existing responses (for connected user only)
        $user     = $this->get('security.context')->getToken()->getUser();
        $reponses = false;
        $remarque = false;
        $resultatEnCours = false;
        if( $user != 'anon.' ) 
        {   
            if( $resultat )
            {
                $resultatEnCours = true;
                $remarque = $resultat->getRemarque();
                $datas = $resultat->getReponses();
                foreach($datas as $one)
                {
                    $reponses[ $one->getQuestion()->getId() ]['value'] = $one->getValue();
                    $reponses[ $one->getQuestion()->getId() ]['remarque'] = $one->getRemarque();
                }
            }
        }

        //Génération du token + mise en session + verif limite domaine
        $passwordTool = new \Nodevo\ToolsBundle\Tools\Password();
        $token = $passwordTool->generate(20,'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $token .= $passwordTool->generate(20,'abcdefghijklmnopqrstuvwyyz');
        $token = str_shuffle($token);

        $sessionToken = $this->get('request')->getSession()->get('token-autodiag-manuel');
        $sessionToken[] = $token;
        $sessionToken = array_values($sessionToken);

        //Pas plus de 100 token pour éviter de 'trop' blinder le serveur
        if(count($sessionToken) > 20)
        {
            unset($sessionToken[0]);
            $sessionToken = array_values($sessionToken);
        }

        $this->get('request')->getSession()->set('token-autodiag-manuel', $sessionToken);

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:outil.html.twig' , array(
            'outil'                 => $outil,
            'resultatEnCours'       => $resultatEnCours,
            'chapitres'             => $chapitresOrdered,
            'reponses'              => $reponses,
            'remarque'              => $remarque,
            'sansGabarit'           => $sansGabarit,
            'resultat'              => $resultat,
            'token_autodiag_manuel' => $token
        ));
    }

    /**
     * Sauvegarde du formulaire outil (save OU valid)
     *
     * @param Outil   $outil   L'entitée Outil
     * @param Request $request La requete
     */
    public function saveAction( Outil $outil, Request $request )
    {
        $token = $request->request->get('_token_manuel_autodiag');

        $sessionToken = $this->get('request')->getSession()->get('token-autodiag-manuel');
        $key = array_search($token, $sessionToken);

        if(is_null($sessionToken)
            ||  $key === false )
        {
            // On envoi une 'flash' pour indiquer à l'utilisateur que l'outil à été enregistré
            $this->get('session')->getFlashBag()->add( 'danger', 'Il semblerait il y avoir un problème dans la sauvegarde de vos données.' );

            return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_outil', array( 'outil' => $outil->getId(), 'alias' => $outil->getAlias() ) ) );
        }
        unset($sessionToken[$key]);
        $this->get('request')->getSession()->set('token-autodiag-manuel', $sessionToken );   


        //get posted Datas
        $chapitres    = $request->request->get($outil->getAlias());
        $remarques    = $request->request->get('remarques-' . $outil->getAlias());
        $action       = $request->request->get('action');
        $remplissage  = $request->request->get('remplissage');
        $nameResultat = $request->request->get('name-resultat');
        $remarque     = $request->request->get('remarque');
        $sansGabarit  = $request->request->get('sansGabarit');
        $newOne       = $request->request->get('newOne');
        $resultat     = $request->request->get('resultat');
        
        //try to get the connected user
        $user = $this->get('security.context')->getToken()->getUser();
        $user = $user != 'anon.' ? $user : false;

        //create Resultat entity
        if( !is_null($resultat) && !$newOne )
        {
            $resultat = $this->get('hopitalnumerique_autodiag.manager.resultat')->findOneBy( array(
                'id'    => $resultat, 
                'outil' => $outil, 
                'user'  => $user 
            ) );
        }
        else 
        {   
            $resultat = false;
            if( $user && !$newOne ) 
            {
                $enCours = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array('id' => 418) );

                //get Resultat for last one note valided
                $resultat = $this->get('hopitalnumerique_autodiag.manager.resultat')->findOneBy( array('outil' => $outil, 'user' => $user, 'statut' => $enCours ) );
            }
        }
        
        //create for the first time
        if( !$resultat ) 
        {
            $resultat = $this->get('hopitalnumerique_autodiag.manager.resultat')->createEmpty();
            $resultat->setOutil( $outil );
        }
        else
        {
            //empty old reponses
            $oldReponses = $this->get('hopitalnumerique_autodiag.manager.reponse')->findBy( array('resultat' => $resultat) );
            $this->get('hopitalnumerique_autodiag.manager.reponse')->delete( $oldReponses );
        }
        
        $resultat->setTauxRemplissage( $remplissage );
        $resultat->setDateLastSave( new \DateTime() );
        $resultat->setRemarque( $remarque );
        if ('' != $nameResultat)
        {
            $resultat->setName( $nameResultat );
        }

        //cas ou l'user à validé le questionnaire
        if( $action == 'valid' )
        {
            $resultat->setDateValidation( new \DateTime() );
            $resultat->setStatut( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 419) ) );
        }
        else
        {
            $resultat->setStatut( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 418) ) );
        }

        //Delete le PDF s'il existe : Permet de le mettre à jour lors de l'affichage des résultats
        if( !is_null($resultat->getPdf()) )
        {
            $pdfName = $resultat->getPdf();
            $resultat->setPdf( null );

            if( file_exists(__ROOT_DIRECTORY__ . '/files/autodiag/' . $pdfName) )
            {
                unlink(__ROOT_DIRECTORY__ . '/files/autodiag/' . $pdfName);
            }
        }

        //cas user connecté
        if( $user )
        {
            $resultat->setUser( $user );
        }

        $this->get('hopitalnumerique_autodiag.manager.resultat')->save( $resultat );

        //Save Réponses
        $reponses = array();
        if(count($chapitres) > 0)
        {
            foreach($chapitres as $chapitre => $questions)
            {
                foreach($questions as $id => $value)
                {
                    //get entity Question
                    $question = $this->get('hopitalnumerique_autodiag.manager.question')->findOneBy( array('id' => $id ) );

                    if(is_null($question))
                    {
                        continue;
                    }

                    //build remarque
                    $remarque = ( isset($remarques[$chapitre]) && isset($remarques[$chapitre][$id]) ) ? $remarques[$chapitre][$id] : '';

                    //create entity Reponse
                    $reponse = $this->get('hopitalnumerique_autodiag.manager.reponse')->createEmpty();
                    $reponse->setQuestion( $question );
                    $reponse->setResultat( $resultat );
                    $reponse->setRemarque( $remarque );
                    $reponse->setValue( trim($value) );

                    $reponses[] = $reponse;
                }
            }

            //Récupération des destinataires dans le fichier de config
            $mailsContact = $this->get('hopitalnumerique_contact.manager.contact')->getMailsContact();

            $variablesTemplate = array(
                    'nomdestinataire'  => '',
                    'maildestinataire' => '',
                    'autodiagnostic'   => $outil->getTitle(),
                    'user'             => $user !== false ? $user->getAppellation() : 'anonyme'
            );
            $mailsAEnvoyer = $this->get('nodevo_mail.manager.mail')->sendAutodiagSauvegardetMail($mailsContact, $variablesTemplate);

            foreach($mailsAEnvoyer as $mailAEnvoyer)
            {
                $this->get('mailer')->send($mailAEnvoyer);
            }

            $this->get('hopitalnumerique_autodiag.manager.reponse')->save( $reponses );
        }

        // On envoi une 'flash' pour indiquer à l'utilisateur que l'outil à été enregistré
        $this->get('session')->getFlashBag()->add( 'success', 'Vos réponses ont bien été sauvegardées.' );

        if( ($action == 'valid' || $action == 'acces_resultats') || !$outil->isCentPourcentReponseObligatoire())
        {
            // si on clique sur "Enregistrer", on reste sur la page "outil"
            if( $action == "save" )
            {
                if($sansGabarit)
                {
                    return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_outil_resultat_sans_gabarit', array( 'outil' => $outil->getId(), 'resultat' => $resultat->getId(), 'sansGabarit' => true ) ) );
                }
                else
                {
                    return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_outil_resultat', array( 'outil' => $outil->getId(), 'resultat' => $resultat->getId(), 'alias' => $outil->getAlias()  ) ) );
                }
            }
            else
            {
                if($sansGabarit)
                {
                    return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_resultat_sans_gabarit', array( 'id' => $resultat->getId(), 'sansGabarit' => true ) ) );
                }
                else
                {
                    return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_resultat', array( 'id' => $resultat->getId() ) ) );
                }
            }
        }
        elseif(!is_null($resultat) && $action != 'acces_resultats')
        {
            if($sansGabarit)
            {
                return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_resultat_sans_gabarit', array( 'id' => $resultat->getId(), 'sansGabarit' => true ) ) );
            }
            else
            {
                return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_outil_resultat', array( 'outil' => $outil->getId(), 'resultat' => $resultat->getId(), 'alias' => $outil->getAlias()  ) ) );
            }   
        }
        else
        {
            if($sansGabarit)
            {
                return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_outil_sans_gabarit', array( 'outil' => $outil->getId(), 'alias' => $outil->getAlias() ) ) );
            }
            else
            {
                return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_outil', array( 'outil' => $outil->getId(), 'alias' => $outil->getAlias() ) ) );
            }
        }

    }

    /**
     * Affiche les résultat d'un outil après la validation d'un outil en front
     *
     * @param  Resultat $resultat L'entitée résultat
     */
    public function resultatAction( Resultat $resultat, $back, Request $request, $sansGabarit = false  )
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $user = $user != 'anon.' ? $user : false;
        
        // si l'autodiagnostic est validé, on ne peut plus revenir à la page d'édition
        if( $resultat->getStatut()->getId() == 419 )
        {
            $back = 1;
        }
      //\Doctrine\Common\Util\Debug::dump($resultat);
        //restriction de l'accès aux résultats lorsque l'user est connecté
        if( 
            ( $user && !is_null($resultat->getUser()) && $resultat->getUser() != $user ) || 
            (!$user && !is_null($resultat->getUser()) ) 
        ) {
            $this->get('session')->getFlashBag()->add( 'danger' , 'Vous n\'avez pas accès à ces résultats.');
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage' ) );
        }

        //récupère les chapitres et les formate pour l'affichage des liens des publications
        $chapitres            = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultat );
        $chapitresForReponse  = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultat );
        $chapitresForAnalyse  = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultat );
        
        //Trier par note
        if ($resultat->getOutil()->isPlanActionPriorise())
        {
            uasort($chapitres, array($this,"triParNote"));
            foreach ($chapitres as $key => $chapitre)
            {
                uasort($chapitre->questions, array($this,"triParNoteQuestion"));
                uasort($chapitre->childs, array($this,"triParNote"));
                foreach ($chapitre->childs as $child)
                {
                    uasort($child->questions, array($this,"triParNoteQuestion"));
                }
            }
        }
        if ($resultat->getOutil()->isPlanActionPriorise())
        {
            uasort($chapitresForAnalyse, array($this,"triParNote"));
            foreach ($chapitresForAnalyse as $key => $chapitre)
            {
                uasort($chapitre->questions, array($this,"triParNoteQuestion"));
                uasort($chapitre->childs, array($this,"triParNote"));
                foreach ($chapitre->childs as $child)
                {
                    uasort($child->questions, array($this,"triParNoteQuestion"));
                }
            }
        }
        //--Analyse

        //Nettoyage des éléments dont il n'y aucun élément
        foreach ($chapitresForAnalyse as $key => $chapitre)
        {
            //Vide le chapitre courant si il a ni de question ni de sous chapitre
            if(empty($chapitre->questions) && empty($chapitre->childs))
            {
                unset($chapitresForAnalyse[$key]);
            }
            //Sinon on cherche parmis les sous chapitres
            elseif(!empty($chapitre->childs))
            {
                $hideChapitre = false;
                foreach ($chapitre->childs as $keyChild => $child) 
                {
                    if(empty($child->questions))
                    {
                        unset($chapitre->childs[$keyChild]);
                        if(empty($chapitre->childs))
                        {
                            $hideChapitre = true;
                        }
                    }
                }

                if($hideChapitre)
                {
                    unset($chapitresForAnalyse[$key]);
                }
            }
        }

        $graphiques = $this->get('hopitalnumerique_autodiag.manager.resultat')->buildCharts( $resultat, $chapitres );
        //Dans le cas où nous nous trouvons dans une synthese, il faut récupérer le min et max
        if ($resultat->getSynthese())
        {
            foreach ($resultat->getResultats() as $resultatSynthese)
            {
                $chapitresSynthese = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultatSynthese );
                $graphTemp = $this->get('hopitalnumerique_autodiag.manager.resultat')->buildCharts( $resultatSynthese, $chapitresSynthese );

                //Radar
                if (array_key_exists('radar', $graphiques))
                {
                    foreach ($graphiques["radar"]->datas as $keyDataGraphique => &$dataGraphique) 
                    {
                        //Récupération de la valeur du graph courant
                        $graphTempValue = $graphTemp["radar"]->datas[$keyDataGraphique]->value;
    
                        if(is_null($dataGraphique->min))
                        {
                            if($graphTempValue != "NC")
                            {
                                $dataGraphique->min = $graphTempValue;
                                $dataGraphique->max = $graphTempValue;
                            }
                        }
                        elseif($graphTempValue == "NC")
                        {
                            if($dataGraphique->max != "NC" )
                            {
                                $dataGraphique->min = $dataGraphique->max;
                            }
                        }
                        elseif($dataGraphique->min > $graphTempValue)
                        {
                            $dataGraphique->min = $graphTempValue;
                        }
                        elseif($dataGraphique->max < $graphTempValue)
                        {
                            $dataGraphique->max = $graphTempValue;
                        }
                    }
                }
                //Barre
                if (array_key_exists('barre', $graphiques))
                {
                    foreach ($graphiques["barre"]->panels as $keyDataGraphique => &$dataGraphique) 
                    {
                        //Récupération de la valeur du graph courant
                        $graphTempValue = $graphTemp["barre"]->panels[$keyDataGraphique]->value;
                        if(is_null($dataGraphique->min))
                        {
                            if($graphTempValue != "NC")
                            {
                                $dataGraphique->min = $graphTempValue;
                                $dataGraphique->max = $graphTempValue;
                            }
                        }
                        elseif($graphTempValue === "NC")
                        {
                            if($dataGraphique->max != "NC" )
                            {
                                $dataGraphique->min = $dataGraphique->max;
                            }
                        }
                        elseif( $dataGraphique->min > $graphTempValue )
                        {
                            $dataGraphique->min = $graphTempValue;
                        }
                        elseif($dataGraphique->max < $graphTempValue)
                        {
                            $dataGraphique->max = $graphTempValue;
                        }
                    }
                }
                // table
                if (array_key_exists('table', $graphiques))
                {
                    foreach ($graphiques["table"]->datas->categories as $keyDataGraphique => &$dataGraphique) 
                    {
                        foreach($dataGraphique['chapitres'] as $id => $chapitre)
                        {
                            //Récupération de la valeur du graph courant
                            $graphTempValue = $graphTemp["table"]->datas->categories[$keyDataGraphique]['chapitres'][$id];
                            if( $graphTempValue['maxPourc'] != 0 )
                            {
                                $value = ( $graphTempValue['nbPointsPourc'] * 100 ) / $graphTempValue['maxPourc'];
    
                                if( !isset($graphiques["table"]->datas->categories[$keyDataGraphique]['chapitres'][$id]['minimum']) 
                                    || $value < $graphiques["table"]->datas->categories[$keyDataGraphique]['chapitres'][$id]['minimum'] 
                                ) {
                                    $graphiques["table"]->datas->categories[$keyDataGraphique]['chapitres'][$id]['minimum'] = $value;
                                }
    
                                if( !isset($graphiques["table"]->datas->categories[$keyDataGraphique]['chapitres'][$id]['maximum']) 
                                    || $value > $graphiques["table"]->datas->categories[$keyDataGraphique]['chapitres'][$id]['maximum'] 
                                ) {
                                    $graphiques["table"]->datas->categories[$keyDataGraphique]['chapitres'][$id]['maximum'] = $value;
                                }
                            }
                        }
                    }
                }
            }
        }

        if ( !$user || $back === 0 )
        {
            $back = false;
        }

        $questionReponseSynthese = $questionReponseSyntheseTableau = $resultatsName = array();
        if($resultat->getSynthese())
        {
            $chapitresSynthese = array();
            //Récupérations de l'ensemble des chapitres de tout les outils de la synthese
            foreach ($resultat->getResultats() as $resultatSynth) 
            {
                $chapitresSynthese[$resultatSynth->getId()]  = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultatSynth );
                $resultatsName[ $resultatSynth->getId() ] = $resultatSynth->getName();
            }
            //Récupérations des réponses aux questions
            foreach ($chapitresSynthese as $resultatId => $chapitresSynthese) 
            {
                $questionReponseSyntheseTableau[$resultatId] = array();
                foreach ($chapitresSynthese as $idChapitreSynth => $chapitreSynthese) 
                {
                    foreach ($chapitreSynthese->questionsBack as $idQuestionChapSynth => $questionSynthese) 
                    {
                        //Init du tableau
                        if(!array_key_exists($questionSynthese->id, $questionReponseSynthese))
                        {
                            $questionReponseSynthese[$questionSynthese->id] = array();
                        }
                        //Init du tableau
                        if(!array_key_exists($questionSynthese->initialValue, $questionReponseSynthese[$questionSynthese->id]))
                        {
                            $questionReponseSynthese[$questionSynthese->id][$questionSynthese->initialValue] = 0;
                        }
                        $questionReponseSynthese[$questionSynthese->id][$questionSynthese->initialValue]++;
                        $questionReponseSyntheseTableau[$resultatId][$questionSynthese->id] = $questionSynthese->initialValue;
                    }

                    foreach ($chapitreSynthese->childs as $chapitreChildSynthese) 
                    {
                        foreach ($chapitreChildSynthese->questionsBack as $idQuestionChapChildSynth => $questionChildSynthese) 
                        {
                            //Init du tableau
                            if(!array_key_exists($questionChildSynthese->id, $questionReponseSynthese))
                            {
                                $questionReponseSynthese[$questionChildSynthese->id] = array();
                            }
                            //Init du tableau
                            if(!array_key_exists($questionChildSynthese->initialValue, $questionReponseSynthese[$questionChildSynthese->id]))
                            {
                                $questionReponseSynthese[$questionChildSynthese->id][$questionChildSynthese->initialValue] = 0;
                            }
                            $questionReponseSynthese[$questionChildSynthese->id][$questionChildSynthese->initialValue]++;
                            $questionReponseSyntheseTableau[$resultatId][$questionChildSynthese->id] = $questionChildSynthese->initialValue;
                        }
                    }
                }  
            }
        }
        
        $radarChartBenchmarkCouleurDecile2 = ($resultat->getOutil()->getRadarChartBenchmarkCouleurDecile2() == 'vert' ? '#76e57e' : '#ff7a7a');
        $radarChartBenchmarkCouleurDecile8 = ($resultat->getOutil()->getRadarChartBenchmarkCouleurDecile8() == 'vert' ? '#76e57e' : '#ff7a7a');

        $options = array(
            'chapitresForAnalyse'     => $chapitresForAnalyse,
            'chapitresForReponse'     => $chapitresForReponse,
            'resultatsName' => $resultatsName,
            'questionReponseSynthese' => $questionReponseSynthese,
            'questionReponseSyntheseTableau' => $questionReponseSyntheseTableau,
            'radarChartBenchmarkCouleurDecile2' => $radarChartBenchmarkCouleurDecile2,
            'radarChartBenchmarkCouleurDecile8' => $radarChartBenchmarkCouleurDecile8
        );

        //PDF généré
        if( is_null($resultat->getPdf()) ){
            $pdf = $this->generatePdf( $chapitres, $graphiques, $resultat, $request, $options );
            $resultat->setPdf( $pdf );
            $this->get('hopitalnumerique_autodiag.manager.resultat')->save( $resultat );
        }

        if(!$resultat->getSynthese() && ($resultat->getOutil()->isCentPourcentReponseObligatoire() && $resultat->getTauxRemplissage() != 100))
        {
            return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_outil', array( 'outil' => $resultat->getOutil()->getId(), 'alias' => $resultat->getOutil()->getAlias() ) ) );
        }
        
        
        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:resultat.html.twig' , array(
            'resultat'                => $resultat,
            'chapitres'               => $chapitres,
            'chapitresForAnalyse'     => $chapitresForAnalyse,
            'chapitresForReponse'     => $chapitresForReponse,
            'questionReponseSynthese' => $questionReponseSynthese,
            'questionReponseSyntheseTableau' => $questionReponseSyntheseTableau,
            'resultatsName'           => $resultatsName,
            'graphiques'              => $graphiques,
            'back'                    => $back,
            'sansGabarit'             => $sansGabarit,
            'radarChartBenchmarkCouleurDecile2' => $radarChartBenchmarkCouleurDecile2,
            'radarChartBenchmarkCouleurDecile8' => $radarChartBenchmarkCouleurDecile8,
            'processusDonnees'        => ($resultat->getOutil()->isProcessChart() ? $this->get('hopitalnumerique_autodiag.manager.process')->getDonneesRestitutionParProcessus($resultat) : null)
        ));
    }

    /**
     * Page Mon Compte : affiche la liste des derniers résultats
     */
    public function autodiagAction()
    {
        $user      = $this->get('security.context')->getToken()->getUser();
        $resultats = $this->get('hopitalnumerique_autodiag.manager.resultat')->findBy( array( 'user' => $user ), array('dateLastSave' => 'DESC') );        

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:autodiag.html.twig' , array(
            'resultats' => $resultats
        ));
    }

    /**
     * Fancy des instructions concernant un outil sur la page du questionnaire
     */
    public function instructionsAction( Outil $outil)
    {       
        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:Fancybox\fancybox.html.twig' , array(
            'outil' => $outil
        ));
    }

    /**
     * Retourne le PDF du résultat
     *
     * @param  Resultat $resultat L'entitée résultat
     */
    public function pdfAction( Resultat $resultat, Request $request )
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $user = $user != 'anon.' ? $user : false;

        //restriction de l'accès aux résultats lorsque l'user est connecté
        if( 
            ( $user && !is_null($resultat->getUser()) && $resultat->getUser() != $user ) || 
            (!$user && !is_null($resultat->getUser()) ) 
        ) {
            $this->get('session')->getFlashBag()->add( 'danger' , 'Vous n\'avez pas accès à ces résultats.');
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage' ) );
        }

        $fileName = __ROOT_DIRECTORY__ . '/files/autodiag/' . $resultat->getPdf();

        //Regenere le pdf si il n'existe plus/pas
        if( is_null($resultat->getPdf()) || !file_exists($fileName) ){
            $back = 0;
            // si l'autodiagnostic est validé, on ne peut plus revenir à la page d'édition
            if( $resultat->getStatut()->getId() == 419 )
            {
                $back = 1;
            }
            //restriction de l'accès aux résultats lorsque l'user est connecté
            if( 
                ( $user && !is_null($resultat->getUser()) && $resultat->getUser() != $user ) || 
                (!$user && !is_null($resultat->getUser()) ) 
            ) {
                $this->get('session')->getFlashBag()->add( 'danger' , 'Vous n\'avez pas accès à ces résultats.');
                return $this->redirect( $this->generateUrl('hopital_numerique_homepage' ) );
            }

            //récupère les chapitres et les formate pour l'affichage des liens des publications
            $chapitres            = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultat );
            $chapitresForReponse  = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultat );
            $chapitresForAnalyse  = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultat );
            
            //Trier par note
            if ($resultat->getOutil()->isPlanActionPriorise())
            {
                uasort($chapitres, array($this,"triParNote"));
                foreach ($chapitres as $key => $chapitre)
                {
                    uasort($chapitre->questions, array($this,"triParNoteQuestion"));
                    uasort($chapitre->childs, array($this,"triParNote"));
                    foreach ($chapitre->childs as $child)
                    {
                        uasort($child->questions, array($this,"triParNoteQuestion"));
                    }
                }
            }
            if ($resultat->getOutil()->isPlanActionPriorise())
            {
                uasort($chapitresForAnalyse, array($this,"triParNote"));
                foreach ($chapitresForAnalyse as $key => $chapitre)
                {
                    uasort($chapitre->questions, array($this,"triParNoteQuestion"));
                    uasort($chapitre->childs, array($this,"triParNote"));
                    foreach ($chapitre->childs as $child)
                    {
                        uasort($child->questions, array($this,"triParNoteQuestion"));
                    }
                }
            }
            //--Analyse

            //Nettoyage des éléments dont il n'y aucun élément
            foreach ($chapitresForAnalyse as $key => $chapitre)
            {
                //Vide le chapitre courant si il a ni de question ni de sous chapitre
                if(empty($chapitre->questions) && empty($chapitre->childs))
                {
                    unset($chapitresForAnalyse[$key]);
                }
                //Sinon on cherche parmis les sous chapitres
                elseif(!empty($chapitre->childs))
                {
                    $hideChapitre = false;
                    foreach ($chapitre->childs as $keyChild => $child) 
                    {
                        if(empty($child->questions))
                        {
                            unset($chapitre->childs[$keyChild]);
                            if(empty($chapitre->childs))
                            {
                                $hideChapitre = true;
                            }
                        }
                    }

                    if($hideChapitre)
                    {
                        unset($chapitresForAnalyse[$key]);
                    }
                }
            }

            $graphiques = $this->get('hopitalnumerique_autodiag.manager.resultat')->buildCharts( $resultat, $chapitres );
            //Dans le cas où nous nous trouvons dans une synthese, il faut récupérer le min et max
            if ($resultat->getSynthese())
            {
                foreach ($resultat->getResultats() as $resultatSynthese)
                {
                    $chapitresSynthese = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultatSynthese );
                    $graphTemp = $this->get('hopitalnumerique_autodiag.manager.resultat')->buildCharts( $resultatSynthese, $chapitresSynthese );

                    //Radar
                    if (array_key_exists('radar', $graphiques))
                    {
                        foreach ($graphiques["radar"]->datas as $keyDataGraphique => &$dataGraphique) 
                        {
                            //Récupération de la valeur du graph courant
                            $graphTempValue = $graphTemp["radar"]->datas[$keyDataGraphique]->value;
        
                            if(is_null($dataGraphique->min))
                            {
                                if($graphTempValue != "NC")
                                {
                                    $dataGraphique->min = $graphTempValue;
                                    $dataGraphique->max = $graphTempValue;
                                }
                            }
                            elseif($graphTempValue == "NC")
                            {
                                if($dataGraphique->max != "NC" )
                                {
                                    $dataGraphique->min = $dataGraphique->max;
                                }
                            }
                            elseif($dataGraphique->min > $graphTempValue)
                            {
                                $dataGraphique->min = $graphTempValue;
                            }
                            elseif($dataGraphique->max < $graphTempValue)
                            {
                                $dataGraphique->max = $graphTempValue;
                            }
                        }
                    }
                    //Barre
                    if (array_key_exists('barre', $graphiques))
                    {
                        foreach ($graphiques["barre"]->panels as $keyDataGraphique => &$dataGraphique) 
                        {
                            //Récupération de la valeur du graph courant
                            $graphTempValue = $graphTemp["barre"]->panels[$keyDataGraphique]->value;
                            if(is_null($dataGraphique->min))
                            {
                                if($graphTempValue != "NC")
                                {
                                    $dataGraphique->min = $graphTempValue;
                                    $dataGraphique->max = $graphTempValue;
                                }
                            }
                            elseif($graphTempValue === "NC")
                            {
                                if($dataGraphique->max != "NC" )
                                {
                                    $dataGraphique->min = $dataGraphique->max;
                                }
                            }
                            elseif( $dataGraphique->min > $graphTempValue )
                            {
                                $dataGraphique->min = $graphTempValue;
                            }
                            elseif($dataGraphique->max < $graphTempValue)
                            {
                                $dataGraphique->max = $graphTempValue;
                            }
                        }
                    }
                    // table
                    if (array_key_exists('table', $graphiques))
                    {
                        foreach ($graphiques["table"]->datas->categories as $keyDataGraphique => &$dataGraphique) 
                        {
                            foreach($dataGraphique['chapitres'] as $id => $chapitre)
                            {
                                //Récupération de la valeur du graph courant
                                $graphTempValue = $graphTemp["table"]->datas->categories[$keyDataGraphique]['chapitres'][$id];
                                if( $graphTempValue['maxPourc'] != 0 )
                                {
                                    $value = ( $graphTempValue['nbPointsPourc'] * 100 ) / $graphTempValue['maxPourc'];
        
                                    if( !isset($graphiques["table"]->datas->categories[$keyDataGraphique]['chapitres'][$id]['minimum']) 
                                        || $value < $graphiques["table"]->datas->categories[$keyDataGraphique]['chapitres'][$id]['minimum'] 
                                    ) {
                                        $graphiques["table"]->datas->categories[$keyDataGraphique]['chapitres'][$id]['minimum'] = $value;
                                    }
        
                                    if( !isset($graphiques["table"]->datas->categories[$keyDataGraphique]['chapitres'][$id]['maximum']) 
                                        || $value > $graphiques["table"]->datas->categories[$keyDataGraphique]['chapitres'][$id]['maximum'] 
                                    ) {
                                        $graphiques["table"]->datas->categories[$keyDataGraphique]['chapitres'][$id]['maximum'] = $value;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ( !$user || $back === 0 )
            {
                $back = false;
            }

            $questionReponseSynthese = $questionReponseSyntheseTableau = $resultatsName = array();
            if($resultat->getSynthese())
            {
                $chapitresSynthese = array();
                //Récupérations de l'ensemble des chapitres de tout les outils de la synthese
                foreach ($resultat->getResultats() as $resultatSynth) 
                {
                    $chapitresSynthese[$resultatSynth->getId()]  = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultatSynth );
                    $resultatsName[ $resultatSynth->getId() ] = $resultatSynth->getName();
                }
                //Récupérations des réponses aux questions
                foreach ($chapitresSynthese as $resultatId => $chapitresSynthese) 
                {
                    $questionReponseSyntheseTableau[$resultatId] = array();
                    foreach ($chapitresSynthese as $idChapitreSynth => $chapitreSynthese) 
                    {
                        foreach ($chapitreSynthese->questionsBack as $idQuestionChapSynth => $questionSynthese) 
                        {
                            //Init du tableau
                            if(!array_key_exists($questionSynthese->id, $questionReponseSynthese))
                            {
                                $questionReponseSynthese[$questionSynthese->id] = array();
                            }
                            //Init du tableau
                            if(!array_key_exists($questionSynthese->initialValue, $questionReponseSynthese[$questionSynthese->id]))
                            {
                                $questionReponseSynthese[$questionSynthese->id][$questionSynthese->initialValue] = 0;
                            }
                            $questionReponseSynthese[$questionSynthese->id][$questionSynthese->initialValue]++;
                            $questionReponseSyntheseTableau[$resultatId][$questionSynthese->id] = $questionSynthese->initialValue;
                        }

                        foreach ($chapitreSynthese->childs as $chapitreChildSynthese) 
                        {
                            foreach ($chapitreChildSynthese->questionsBack as $idQuestionChapChildSynth => $questionChildSynthese) 
                            {
                                //Init du tableau
                                if(!array_key_exists($questionChildSynthese->id, $questionReponseSynthese))
                                {
                                    $questionReponseSynthese[$questionChildSynthese->id] = array();
                                }
                                //Init du tableau
                                if(!array_key_exists($questionChildSynthese->initialValue, $questionReponseSynthese[$questionChildSynthese->id]))
                                {
                                    $questionReponseSynthese[$questionChildSynthese->id][$questionChildSynthese->initialValue] = 0;
                                }
                                $questionReponseSynthese[$questionChildSynthese->id][$questionChildSynthese->initialValue]++;
                                $questionReponseSyntheseTableau[$resultatId][$questionChildSynthese->id] = $questionChildSynthese->initialValue;
                            }
                        }
                    }  
                }
            }
            
            $radarChartBenchmarkCouleurDecile2 = ($resultat->getOutil()->getRadarChartBenchmarkCouleurDecile2() == 'vert' ? '#76e57e' : '#ff7a7a');
            $radarChartBenchmarkCouleurDecile8 = ($resultat->getOutil()->getRadarChartBenchmarkCouleurDecile8() == 'vert' ? '#76e57e' : '#ff7a7a');

            $options = array(
                'chapitresForAnalyse'     => $chapitresForAnalyse,
                'chapitresForReponse'     => $chapitresForReponse,
                'resultatsName' => $resultatsName,
                'questionReponseSynthese' => $questionReponseSynthese,
                'questionReponseSyntheseTableau' => $questionReponseSyntheseTableau,
                'radarChartBenchmarkCouleurDecile2' => $radarChartBenchmarkCouleurDecile2,
                'radarChartBenchmarkCouleurDecile8' => $radarChartBenchmarkCouleurDecile8
            );

            $pdf = $this->generatePdf( $chapitres, $graphiques, $resultat, $request, $options );
            $resultat->setPdf( $pdf );
            $this->get('hopitalnumerique_autodiag.manager.resultat')->save( $resultat );

            $fileName = __ROOT_DIRECTORY__ . '/files/autodiag/' . $resultat->getPdf();
        }

        $options  = array(
            'serve_filename' => 'resultat-outil-'.$resultat->getOutil()->getAlias().'.pdf',
            'absolute_path'  => false,
            'inline'         => false,
        );

        return $this->get('igorw_file_serve.response_factory')->create( $fileName , 'application/pdf', $options);
    }

    /**
     * Valide un résultat
     *
     * @param  Resultat $resultat L'entitée résultat
     */
    public function validateAction( Resultat $resultat, Request $request )
    {
        $reference = $this->get('hopitalnumerique_reference.manager.reference')->findOneById( 419 );
        $resultat->setStatut($reference);
        $resultat->setDateValidation( new \DateTime() );
        $this->get('hopitalnumerique_autodiag.manager.resultat')->save( $resultat );
        
        $this->get('session')->getFlashBag()->add( 'success', 'Votre autodiagnostic a bien été ajouté à votre historique.' );
        return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_resultat', array('id' => $resultat->getId())) );
    }

    /**
     * Reactive un résultat
     *
     * @param  Resultat $resultat L'entitée résultat
     */
    public function reactivateAction( Resultat $resultat, Request $request )
    {
        $reference = $this->get('hopitalnumerique_reference.manager.reference')->findOneById( 418 );
        $resultat->setStatut($reference);
        $resultat->setDateValidation(null);
        $this->get('hopitalnumerique_autodiag.manager.resultat')->save( $resultat );
        
        $this->get('session')->getFlashBag()->add( 'success', 'Votre autodiagnostic a bien été réactivé.' );
        return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_comptehn') );
    }

    /**
     * Supprime un résultat
     *
     * @param  Resultat $resultat [description]
     *
     * @return [type]
     */
    public function deleteAction( Resultat $resultat )
    {
        //Delete le PDF s'il existe
        if( !is_null($resultat->getPdf()) && file_exists(__ROOT_DIRECTORY__ . '/files/autodiag/' . $resultat->getPdf() ) )
            unlink(__ROOT_DIRECTORY__ . '/files/autodiag/' . $resultat->getPdf() );

        $this->get('hopitalnumerique_autodiag.manager.resultat')->delete( $resultat );

        // On envoi une 'flash' pour indiquer à l'utilisateur que l'outil à été enregistré
        $this->get('session')->getFlashBag()->add( 'success', 'Résultats supprimés.');

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_autodiag_front_comptehn').'"}', 200);
    }

    /**
     * Supprime un partage de résultat
     *
     * @param  Resultat $resultat [description]
     *
     * @return [type]
     */
    public function deletePartageAction( Resultat $resultat )
    {
        $this->get('hopitalnumerique_autodiag.manager.resultat')->delete( $resultat->getResultatSharedFor() );

        // On envoi une 'flash' pour indiquer à l'utilisateur que l'outil à été enregistré
        $this->get('session')->getFlashBag()->add( 'success', 'Partage supprimé.');

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_autodiag_front_comptehn').'"}', 200);
    }

    /**
     * Génère la synthèse d'un groupe de résultat
     *
     * @return empty
     */
    public function syntheseAction( Request $request )
    {
        //create Synthese Object
        $user     = $this->get('security.context')->getToken()->getUser();
        $outil    = $this->get('hopitalnumerique_autodiag.manager.outil')->findOneBy( array( 'id' => $request->request->get('outil') ) );
        $statut   = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 419 ) );
        $synthese = $this->get('hopitalnumerique_autodiag.manager.resultat')->buildSynthese( $user, $outil, $statut, $request->request->get('nom') );
        
        //generate Reponses
        $this->buildNewReponses( $request->request->get('resultats'), $synthese );

        $this->get('session')->getFlashBag()->add( 'success', 'Synthèse créée.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_autodiag_front_resultat', array('id' => $synthese->getId(), 'back' => 1 )).'"}', 200);
    }














    /**
     * Trie par note une stdClass
     *
     * @param [type] $a [description]
     * @param [type] $b [description]
     *
     * @return [type]
     */
    public function triParNote($a, $b)
    {
        if($a->noteChapitre < $b->noteChapitre)
            return -1;
        if($a->noteChapitre > $b->noteChapitre)
            return 1;
        if($a->order > $b->order)
            return 1;
        else
            return -1;
    }
    /**
     * Trie par note une stdClass
     *
     * @param [type] $a [description]
     * @param [type] $b [description]
     *
     * @return [type]
     */
    public function triParNoteQuestion($a, $b)
    {
        if($a->value < $b->value)
            return -1;
        if($a->value > $b->value)
            return 1;
        if($a->order > $b->order)
            return 1;
        else
            return -1;
    }
    /**
     * Trie pour le graph tableau
     *
     * @param [type] $a [description]
     * @param [type] $b [description]
     *
     * @return [type]
     */
    public function triParOrderGraphTable($a, $b)
    {
        if($a['order'] < $b['order'])
            return -1;
        if($a['order'] > $b['order'])
            return 1;
    }

    /**
     * Prépare le tableau de réponse, effectue les calculs de moyenne et ajoute les réponses
     *
     * @param array    $resultats Liste des Résultats
     * @param Resultat $synthese  Objet Synthese
     *
     * @return empty
     */
    private function buildNewReponses( $resultats, $synthese )
    {
        $resultats = $this->get('hopitalnumerique_autodiag.manager.resultat')->findBy( array( 'id' => $resultats ) );
        $syntheseReponses = array();

        foreach( $resultats as $resultat ) {
            $reponses = $resultat->getReponses();
            foreach( $reponses as $reponse) {
                //prepare entry
                if( !isset( $syntheseReponses[ $reponse->getQuestion()->getId() ] ) )
                    $syntheseReponses[ $reponse->getQuestion()->getId() ] = array();
                //add entry
                $syntheseReponses[ $reponse->getQuestion()->getId() ][] = $reponse;
            }

            //link Resultat object
            $synthese->addResultat( $resultat );
        }

        $moyennes = array();
        foreach($syntheseReponses as $idQuestion => $reponses ){
            //get entity Question
            $question = $this->get('hopitalnumerique_autodiag.manager.question')->findOneBy( array('id' => $idQuestion ) );

            $nbVal = 0;
            $val   = 0;
            $exist = false;
            $isNC  = true;

            //calc moyenne
            foreach($reponses as $reponse)
            {
                if ( $reponse->getValue() != -1 && $reponse->getValue() != '' )
                {
                    $val += $reponse->getValue() != '' ? $reponse->getValue() : 0;
                    $nbVal++;
                    $exist = true;
                }

                if($reponse->getValue() != -1)
                {
                    $isNC = false;
                }
            }
            if ($exist)
            	$val = $nbVal != 0 ? ( $val / $nbVal ) : -1;
            elseif($isNC)
                $val = -1;
            else
                $val = '';

            //create entity Reponse
            $rep = $this->get('hopitalnumerique_autodiag.manager.reponse')->createEmpty();
            $rep->setQuestion( $question );
            $rep->setResultat( $synthese );
            $rep->setRemarque( '' );
            $rep->setValue( $val );

            $moyennes[] = $rep;
        }

        $this->get('hopitalnumerique_autodiag.manager.reponse')->save( $moyennes );
    }

    /**
     * Génère un pdf pour le résultat
     *
     * @param array    $chapitres  Liste des chapitres
     * @param array    $graphiques Liste des graphiques
     * @param Resultat $resultat   Objet résultat
     * @param Request  $request    Objet Request
     *
     * @return string PDF name
     */
    private function generatePdf( $chapitres, $graphiques, $resultat, $request , $options)
    {
        $filename = $resultat->getId() . $resultat->getOutil()->getId() . time() . '.pdf';
        
        $html = $this->renderView( 'HopitalNumeriqueAutodiagBundle:Front:pdf.html.twig' , array(
            'resultat'                => $resultat,
            'chapitres'               => $chapitres,
            'chapitresForAnalyse'     => $options["chapitresForAnalyse"],
            'chapitresForReponse'     => $options["chapitresForReponse"],
            'questionReponseSynthese' => $options["questionReponseSynthese"],
            'questionReponseSyntheseTableau' => $options["questionReponseSyntheseTableau"],
            'resultatsName'           => $options["resultatsName"],
            'graphiques'              => $graphiques,
            'radarChartBenchmarkCouleurDecile2' => $options["radarChartBenchmarkCouleurDecile2"],
            'radarChartBenchmarkCouleurDecile8' => $options["radarChartBenchmarkCouleurDecile8"],
            'processusDonnees'        => ($resultat->getOutil()->isProcessChart() ? $this->get('hopitalnumerique_autodiag.manager.process')->getDonneesRestitutionParProcessus($resultat) : null)
        ));

        $toolTitle = new Chaine($resultat->getOutil()->getTitle());
        $toolName  = new Chaine($resultat->getName());

        $options = array(
            // 'orientation'      => 'landscape',
            'encoding'         => 'UTF-8',
            'javascript-delay' => 500,
            'margin-top'       => '10',
            'margin-bottom'    => '30',
            'margin-right'     => '15',
            'margin-left'      => '15',
            'header-spacing'   => '2',
            'header-right'     => 'Page [page]/[toPage]',
            'header-font-size' => '10',
            'header-left'      => $toolTitle->supprimeAccents() . ' - ' . (trim($toolName->supprimeAccents()) != "" ? ($toolName->supprimeAccents() . ' - ') : '') . $resultat->getDateLastSave()->format('d/m/Y') ,
            'footer-spacing'   => '10',
            'footer-html'      => '<p style="font-size:10px;text-align:center;color:#999">
                                     &copy; ANAP<br/>
                                     Ces contenus extraits de l\'ANAP sont diffus&eacute;s gratuitement.<br/>
                                     Toutefois, leur utilisation ou citation est soumise &agrave; l\'inscription de la mention suivante : "&copy; ANAP"'
        );

        $html = str_replace('/publication', $request->getSchemeAndHttpHost() . '/publication', $html);

        $this->get('knp_snappy.pdf')->generateFromHtml(
            $html,
            __ROOT_DIRECTORY__ . '/files/autodiag/' . $filename,
            $options
        );

        return $filename;
    }
}
