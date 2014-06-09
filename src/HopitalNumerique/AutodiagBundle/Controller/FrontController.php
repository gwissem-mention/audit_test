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
     * Affiche le Front vu chapitre
     *
     * @param Outil $outil L'entitée Outil
     */
    public function outilAction( Outil $outil )
    {
        //init some vars
        $ponderationMax = 100;
        $questions      = array();
        $chapitres      = $outil->getChapitres();
        $parents        = array();
        $enfants        = array();
        
        //build big array of questions
        foreach($chapitres as $chapitre){
            $questions = array_merge($questions, $chapitre->getQuestions()->toArray() );

            if( is_null($chapitre->getParent()) ){
                $parents[ $chapitre->getId() ]['parent'] = $chapitre;
                $parents[ $chapitre->getId() ]['childs'] = array();
            }else
                $enfants[] = $chapitre;
        }
        
        //calcul pondération
        foreach( $questions as $key => $question) {
            $ponderation = $question->getPonderation();

            if( $ponderation != 0 )
                $ponderationMax -= $ponderation;
        }

        //max Pondération invalid
        if( $ponderationMax != 0 ) {
            // On envoi une 'flash' pour indiquer à l'utilisateur que l'outil à été enregistré
            $this->get('session')->getFlashBag()->add( 'danger', 'L\'outil n\'est pas correctement configué, il n\'est donc pas accessible');
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage'));
        }

        //reformate les chapitres
        foreach($enfants as $enfant) {
            $parentId = $enfant->getParent()->getId();
            $parents[ $parentId ]['childs'][] = $enfant;
        }

        //get Existing responses (for connected user only)
        $user     = $this->get('security.context')->getToken()->getUser();
        $reponses = false;
        if( $user != 'anon.' ) {
            $enCours = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array('id' => 418) );
            
            //get Resultat for last one note valided
            $resultat = $this->get('hopitalnumerique_autodiag.manager.resultat')->findOneBy( array('outil' => $outil, 'user' => $user, 'statut' => $enCours ) );
            
            //if the previous one is valided, we get the results to pre-load values
            if( !$resultat )
                $resultat = $this->get('hopitalnumerique_autodiag.manager.resultat')->getLastResultatValided( $outil, $user );

            if( $resultat ){
                $datas = $resultat->getReponses();
                foreach($datas as $one)
                    $reponses[ $one->getQuestion()->getId() ] = $one->getValue();
            }
        }

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:outil.html.twig' , array(
            'outil'     => $outil,
            'chapitres' => $parents,
            'reponses'  => $reponses
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
        $chapitres   = $request->request->get( $outil->getAlias() );
        $remarques   = $request->request->get( 'remarques-' . $outil->getAlias() );
        $action      = $request->request->get('action');
        $remplissage = $request->request->get('remplissage');

        //try to get the connected user
        $user = $this->get('security.context')->getToken()->getUser();
        $user = $user != 'anon.' ? $user : false;

        //create Resultat entity
        if( $user ) {
            $enCours = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array('id' => 418) );
            
            //get Resultat for last one note valided
            $resultat = $this->get('hopitalnumerique_autodiag.manager.resultat')->findOneBy( array('outil' => $outil, 'user' => $user, 'statut' => $enCours ) );
        }
        
        //create for the first time
        if( !$resultat ) {
            $resultat = $this->get('hopitalnumerique_autodiag.manager.resultat')->createEmpty();
            $resultat->setOutil( $outil );
        }else{
            //empty old reponses
            $oldReponses = $this->get('hopitalnumerique_autodiag.manager.reponse')->findBy( array('resultat'=>$resultat) );
            $this->get('hopitalnumerique_autodiag.manager.reponse')->delete( $oldReponses );
        }
        
        $resultat->setTauxRemplissage( $remplissage );
        $resultat->setDateLastSave( new \DateTime() );

        //cas ou l'user à validé le questionnaire
        if( $action == 'valid'){
            $resultat->setDateValidation( new \DateTime() );
            $resultat->setStatut( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 419) ) );
        }else
            $resultat->setStatut( $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 418) ) );

        //cas user connecté
        if( $user )
            $resultat->setUser( $user );

        $this->get('hopitalnumerique_autodiag.manager.resultat')->save( $resultat );

        //Save Réponses
        $reponses = array();
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

        // On envoi une 'flash' pour indiquer à l'utilisateur que l'outil à été enregistré
        $this->get('session')->getFlashBag()->add( 'success', 'Autodiagnostic ' . ($action == 'valid' ? 'validé':'enregistré') );

        return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_resultat', array( 'id' => $resultat->getId() ) ) );
    }

    /**
     * Affiche les résultat d'un outil après la validation d'un outil en front
     *
     * @param  Resultat $resultat L'entitée résultat
     */
    public function resultatAction( Resultat $resultat )
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $user = $user != 'anon.' ? $user : false;

        //restriction de l'accès aux résultats lorsque l'user est connecté
        if( 
            ( $user && !is_null($resultat->getUser()) && $resultat->getUser() != $user ) || 
            (!$user && !is_null($resultat->getUser()) ) 
        ) {
            $this->get('session')->getFlashBag()->add( 'danger' , 'Vous n\'avez pas accès à ces résultats');
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage' ) );
        }

        //récupère les chapitres et les formate pour l'affichage des liens des publications
        $chapitres  = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultat, true );
        $graphiques = $this->get('hopitalnumerique_autodiag.manager.resultat')->buildCharts( $resultat, $chapitres );

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:resultat.html.twig' , array(
            'resultat'   => $resultat,
            'chapitres'  => $chapitres,
            'graphiques' => $graphiques
        ));
    }

    /**
     * Génère le PDF de résultat
     *
     * @param Resultat $resultat L'entitée Résultat
     *
     * @return PDF
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
            $this->get('session')->getFlashBag()->add( 'danger' , 'Vous n\'avez pas accès à ces résultats');
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage' ) );
        }

        //récupère les chapitres et les formate pour l'affichage des liens des publications
        $chapitres  = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultat, true );
        $graphiques = $this->get('hopitalnumerique_autodiag.manager.resultat')->buildCharts( $resultat, $chapitres );

        $html = $this->renderView( 'HopitalNumeriqueAutodiagBundle:Front:pdf.html.twig' , array(
            'resultat'   => $resultat,
            'chapitres'  => $chapitres,
            'graphiques' => $graphiques
        ));

        $options = array(
            'margin-bottom'    => 0,
            'margin-left'      => 5,
            'margin-right'     => 5,
            'margin-top'       => 4,
            'encoding'         => 'UTF-8',
            'javascript-delay' => 500
        );

        $html = str_replace('/publication', $request->getSchemeAndHttpHost() . '/publication', $html);

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html, $options, true),
            200,
            array(
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="resultats-de-l-outil-'.$resultat->getOutil()->getAlias().'.pdf"'
            )
        );
    }

    /**
     * Page Mon Compte : affiche la liste des derniers résultats
     */
    public function autodiagAction()
    {
        $user      = $this->get('security.context')->getToken()->getUser();
        $resultats = $this->get('hopitalnumerique_autodiag.manager.resultat')->findBy( array( 'user' => $user ) );        

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:autodiag.html.twig' , array(
            'resultats' => $resultats
        ));
    }
}