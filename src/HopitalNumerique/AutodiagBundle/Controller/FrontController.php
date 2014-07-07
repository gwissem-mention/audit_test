<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\AutodiagBundle\Entity\Resultat;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
    public function outilAction( Outil $outil )
    {
        //init some vars
        $chapitres      = $outil->getChapitres();
        $parents        = array();
        $enfants        = array();
        
        //build chapitres
        foreach($chapitres as $chapitre){
            if( is_null($chapitre->getParent()) ){
                $parents[ $chapitre->getId() ]['parent'] = $chapitre;
                $parents[ $chapitre->getId() ]['childs'] = array();
            }else
                $enfants[] = $chapitre;
        }

        //reformate les chapitres
        foreach($enfants as $enfant) {
            $parentId = $enfant->getParent()->getId();
            $parents[ $parentId ]['childs'][$enfant->getOrder()] = $enfant;
        }

        //reorder parents
        $chapitresOrdered = array();
        foreach($parents as $one){
            $tmp = $one['parent'];

            //sort childs
            ksort($one['childs']);

            $chapitresOrdered[ $tmp->getOrder() ] = $one;
        }
        ksort($chapitresOrdered);

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
                foreach($datas as $one){
                    $reponses[ $one->getQuestion()->getId() ]['value'] = $one->getValue();
                    $reponses[ $one->getQuestion()->getId() ]['remarque'] = $one->getRemarque();
                }
            }
        }

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:outil.html.twig' , array(
            'outil'     => $outil,
            'chapitres' => $chapitresOrdered,
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
        $resultat = false;
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

        //Delete le PDF s'il existe : Permet de le mettre à jour lors de l'affichage des résultats
        if( !is_null($resultat->getPdf()) ){
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
        $this->get('session')->getFlashBag()->add( 'success', 'Autodiagnostic ' . ($action == 'valid' ? 'validé.':'enregistré.') );

        return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_front_resultat', array( 'id' => $resultat->getId() ) ) );
    }

    /**
     * Affiche les résultat d'un outil après la validation d'un outil en front
     *
     * @param  Resultat $resultat L'entitée résultat
     */
    public function resultatAction( Resultat $resultat, Request $request )
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
        $chapitres  = $this->get('hopitalnumerique_autodiag.manager.resultat')->formateResultat( $resultat, true );
        $graphiques = $this->get('hopitalnumerique_autodiag.manager.resultat')->buildCharts( $resultat, $chapitres );

        //PDF généré
        if( is_null($resultat->getPdf()) ){
            $pdf = $this->generatePdf( $chapitres, $graphiques, $resultat, $request );
            $resultat->setPdf( $pdf );
            $this->get('hopitalnumerique_autodiag.manager.resultat')->save( $resultat );
        }

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Front:resultat.html.twig' , array(
            'resultat'   => $resultat,
            'chapitres'  => $chapitres,
            'graphiques' => $graphiques
        ));
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
     * Génère un pdf pour le résultat
     *
     * @param array    $chapitres  Liste des chapitres
     * @param array    $graphiques Liste des graphiques
     * @param Resultat $resultat   Objet résultat
     * @param Request  $request    Objet Request
     *
     * @return string PDF name
     */
    private function generatePdf( $chapitres, $graphiques, $resultat, $request )
    {
        $filename = $resultat->getId() . $resultat->getOutil()->getId() . time() . '.pdf';

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

        $this->get('knp_snappy.pdf')->generateFromHtml(
            $html,
            __ROOT_DIRECTORY__ . '/files/autodiag/' . $filename,
            $options
        );

        return $filename;
    }
}