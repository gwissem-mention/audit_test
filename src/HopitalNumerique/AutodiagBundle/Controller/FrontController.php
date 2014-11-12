<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\AutodiagBundle\Entity\Resultat;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * Affiche le Front vu chapitre
     *
     * @param Outil $outil L'entitée Outil
     */
    public function outilAction( Outil $outil, $sansGabarit = false )
    {
        //init some vars
        $chapitres      = $outil->getChapitres();
        $parents        = array();
        $enfants        = array();
        
        //build chapitres
        foreach($chapitres as $chapitre)
        {
            //Si on ne doit pas afficher le chapitre, on ne le prend pas en compte
            if( is_null($chapitre->getParent()) ){
                $parents[ $chapitre->getId() ]['parent'] = $chapitre;
                $parents[ $chapitre->getId() ]['childs'] = array();
            }else
                $enfants[] = $chapitre;
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
        if( $user != 'anon.' ) 
        {
            $enCours = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array('id' => 418) );
            
            //get Resultat for last one note valided
            $resultat = $this->get('hopitalnumerique_autodiag.manager.resultat')->findOneBy( array('outil' => $outil, 'user' => $user, 'statut' => $enCours ) );
            
            //if the previous one is valided, we get the results to pre-load values
            if( !$resultat )
            {
                $resultat = $this->get('hopitalnumerique_autodiag.manager.resultat')->getLastResultatValided( $outil, $user );
            }

            if( $resultat )
            {
                $remarque = $resultat->getRemarque();
                $datas = $resultat->getReponses();
                foreach($datas as $one)
                {
                    $reponses[ $one->getQuestion()->getId() ]['value'] = $one->getValue();
                    $reponses[ $one->getQuestion()->getId() ]['remarque'] = $one->getRemarque();
                }
            }
        }

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:outil.html.twig' , array(
            'outil'       => $outil,
            'chapitres'   => $chapitresOrdered,
            'reponses'    => $reponses,
            'remarque'    => $remarque,
            'sansGabarit' => $sansGabarit
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
        //get posted Datas
        $chapitres    = $request->request->get($outil->getAlias());
        $remarques    = $request->request->get('remarques-' . $outil->getAlias());
        $action       = $request->request->get('action');
        $remplissage  = $request->request->get('remplissage');
        $nameResultat = $request->request->get('name-resultat');
        $remarque     = $request->request->get('remarque');
        $sansGabarit  = $request->request->get('sansGabarit');

        //try to get the connected user
        $user = $this->get('security.context')->getToken()->getUser();
        $user = $user != 'anon.' ? $user : false;

        //create Resultat entity
        $resultat = false;
        if( $user ) 
        {
            $enCours = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array('id' => 418) );
            
            //get Resultat for last one note valided
            $resultat = $this->get('hopitalnumerique_autodiag.manager.resultat')->findOneBy( array('outil' => $outil, 'user' => $user, 'statut' => $enCours ) );
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

        //cas ou l'user à validé le questionnaire
        if( $action == 'valid')
        {
            $resultat->setName( $nameResultat );
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
                unlink(__ROOT_DIRECTORY__ . '/files/autodiag/' . $pdfName);
        }

        //cas user connecté
        if( $user )
            $resultat->setUser( $user );

        $this->get('hopitalnumerique_autodiag.manager.resultat')->save( $resultat );

        //Save Réponses
        $reponses = array();
        if(count($chapitres) > 0)
        {
            foreach($chapitres as $chapitre => $questions) {
                foreach($questions as $id => $value) {
                    //get entity Question
                    $question = $this->get('hopitalnumerique_autodiag.manager.question')->findOneBy( array('id' => $id ) );

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
            $this->get('hopitalnumerique_autodiag.manager.reponse')->save( $reponses );
        }

        // On envoi une 'flash' pour indiquer à l'utilisateur que l'outil à été enregistré
        $this->get('session')->getFlashBag()->add( 'success', 'Autodiagnostic ' . ($action == 'valid' ? 'validé.':'enregistré.') );


        if( $action == 'valid' || !$outil->isCentPourcentReponseObligatoire())
        {
            if($sansGabarit)
                return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_resultat_sans_gabarit', array( 'id' => $resultat->getId(), 'sansGabarit' => true ) ) );
            else
                return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_resultat', array( 'id' => $resultat->getId() ) ) );
        }
        else
        {
            if($sansGabarit)
                return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_outil_sans_gabarit', array( 'outil' => $outil->getId(), 'alias' => $outil->getAlias() ) ) );
            else
                return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_outil', array( 'outil' => $outil->getId(), 'alias' => $outil->getAlias() ) ) );
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
        if($resultat->getOutil()->isPlanActionPriorise())
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
        if($resultat->getOutil()->isPlanActionPriorise())
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
        if($resultat->getSynthese())
        {
            foreach ($resultat->getResultats() as $resultatSynthese) 
            {
                $chapitresSynthese = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultatSynthese );
                $graphTemp = $this->get('hopitalnumerique_autodiag.manager.resultat')->buildCharts( $resultatSynthese, $chapitresSynthese );                

                //Radar
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
                //Barre
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
        }

        if( !$user || $back === 0 )
            $back = false;

        $questionReponseSynthese = array();
        if($resultat->getSynthese())
        {
            $chapitresSynthese = array();
            //Récupérations de l'ensemble des chapitres de tout les outils de la synthese
            foreach ($resultat->getResultats() as $resultatSynth) 
            {
                $chapitresSynthese[$resultatSynth->getId()]  = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultatSynth );
            }
            //Récupérations des réponses aux questions
            foreach ($chapitresSynthese as $resultatId => $chapitresSynthese) 
            {
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
                        }
                    }
                }  
            }
        }

        $options = array(
            'chapitresForAnalyse'     => $chapitresForAnalyse,
            'chapitresForReponse'     => $chapitresForReponse,
            'questionReponseSynthese' => $questionReponseSynthese,
        );

        //PDF généré
        if( is_null($resultat->getPdf()) ){
            $pdf = $this->generatePdf( $chapitres, $graphiques, $resultat, $request, $options );
            $resultat->setPdf( $pdf );
            $this->get('hopitalnumerique_autodiag.manager.resultat')->save( $resultat );
        }

        if($resultat->getOutil()->isCentPourcentReponseObligatoire() && $resultat->getTauxRemplissage() != 100)
        {
            return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_outil', array( 'outil' => $resultat->getOutil()->getId(), 'alias' => $resultat->getOutil()->getAlias() ) ) );
        }

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:resultat.html.twig' , array(
            'resultat'                => $resultat,
            'chapitres'               => $chapitres,
            'chapitresForAnalyse'     => $chapitresForAnalyse,
            'chapitresForReponse'     => $chapitresForReponse,
            'questionReponseSynthese' => $questionReponseSynthese,
            'graphiques'              => $graphiques,
            'back'                    => $back,
            'sansGabarit'             => $sansGabarit,
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
        $options  = array(
            'serve_filename' => 'resultat-outil-'.$resultat->getOutil()->getAlias().'.pdf',
            'absolute_path'  => false,
            'inline'         => false,
        );

        return $this->get('igorw_file_serve.response_factory')->create( $fileName , 'application/pdf', $options);
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
            foreach($reponses as $reponse) {
                if ( $reponse->getValue() != -1 && $reponse->getValue() != ''){
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
            'graphiques'              => $graphiques,
            'processusDonnees'        => ($resultat->getOutil()->isProcessChart() ? $this->get('hopitalnumerique_autodiag.manager.process')->getDonneesRestitutionParProcessus($resultat) : null)
        ));

        $options = array(
            'orientation'      => 'landscape',
            'encoding'         => 'UTF-8',
            'javascript-delay' => 500,
            'margin-top'       => '10',
            'margin-bottom'    => '30',
            'margin-right'     => '15',
            'margin-left'      => '15',
            'header-spacing'   => '2',
            'header-right'     => 'Page [page]/[toPage]',
            'footer-spacing'   => '10',
            'footer-html'      => '<p style="font-size:10px;text-align:center;color:#999">
                                     &copy; ANAP - Mon H&ocirc;pital Num&eacute;rique<br/>
                                     Ces contenus extraits de la plateforme de l\'Anap www.MonHopitalNumerique.fr sont diffus&eacute;s gratuitement.<br/>
                                     Toutefois, leur utilisation ou citation est soumise &agrave; l\'inscription de la mention suivante : "&copy; ANAP - Mon H&ocirc;pital Num&eacute;rique"'
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
