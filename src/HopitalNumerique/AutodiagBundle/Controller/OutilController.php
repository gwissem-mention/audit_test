<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\AutodiagEvents;
use HopitalNumerique\AutodiagBundle\Entity\Outil;
use HopitalNumerique\AutodiagBundle\Event\OutilEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Outil controller.
 */
class OutilController extends Controller
{
    /**
     * Affiche la liste des Outil.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_autodiag.grid.outil');

        return $grid->render('HopitalNumeriqueAutodiagBundle:Outil:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de Outil.
     */
    public function addAction(Request $request)
    {
        $outil = $this->get('hopitalnumerique_autodiag.manager.outil')->createEmpty();

        //Création du formulaire via le service
        $form = $this->createForm('hopitalnumerique_autodiag_outil', $outil );

        //si le formulaire est valide
        if ( $form->handleRequest($request)->isValid() ) {
            //Save
            $this->get('hopitalnumerique_autodiag.manager.outil')->saveOutil($outil);
            
            // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
            $this->get('session')->getFlashBag()->add( 'success' , 'Outil ajouté'); 
            
            return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_outil_edit', array( 'id' => $outil->getId() ) ) );
        }

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Outil:edit.html.twig' , array(
            'form'  => $form->createView(),
            'outil' => $outil
        ));
    }

    /**
     * Affiche le formulaire d'édition de Outil.
     *
     * @param integer $id Id de Outil.
     */
    public function editAction( Outil $outil, Request $request )
    {
        //Création du formulaire via le service
        $form = $this->createForm( 'hopitalnumerique_autodiag_outil', $outil);
        
        //si le formulaire est valide
        if ( $form->handleRequest($request)->isValid() ) {
            //Save
            $this->get('hopitalnumerique_autodiag.manager.outil')->saveOutil($outil);

            // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
            $this->get('session')->getFlashBag()->add( 'info' , 'Outil mis à jour.' ); 
            
            //on redirige vers la page index ou la page edit selon le bouton utilisé
            $do = $request->request->get('do');
            return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_autodiag_outil') : $this->generateUrl('hopitalnumerique_autodiag_outil_edit', array( 'id' => $outil->getId() ) ) ) );
        }

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Outil:edit.html.twig' , array(
            'form'  => $form->createView(),
            'outil' => $outil
        ));
    }

    /**
     * Suppresion d'un Outil.
     * 
     * @param integer $id Id de Outil.
     * METHOD = POST|DELETE
     */
    public function deleteAction( Outil $outil )
    {
        $this->get('hopitalnumerique_autodiag.manager.outil')->delete( $outil );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_autodiag_outil').'"}', 200);
    }

    /**
     * Désctivation de masse des outils
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function desactiverMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected outils
        $outils = $this->get('hopitalnumerique_autodiag.manager.outil')->findBy( array('id' => $primaryKeys) );

        //get ref and Toggle State
        $ref = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 4 ) );
        $this->get('hopitalnumerique_autodiag.manager.outil')->toogleState( $outils, $ref );

        //inform user connected
        $this->get('session')->getFlashBag()->add('info', 'Désactivation effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_outil') );
    }

    /**
     * Activation de masse des outils
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function activerMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected outils
        $outils = $this->get('hopitalnumerique_autodiag.manager.outil')->findBy( array('id' => $primaryKeys) );

        //get ref and Toggle State
        $ref = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 3 ) );
        $this->get('hopitalnumerique_autodiag.manager.outil')->toogleState( $outils, $ref );

        //inform user connected
        $this->get('session')->getFlashBag()->add('info', 'Activation effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_outil') );
    }

    /**
     * Suppression de masse des outils
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function deleteMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected outils
        $outils = $this->get('hopitalnumerique_autodiag.manager.outil')->findBy( array('id' => $primaryKeys) );
        $this->get('hopitalnumerique_autodiag.manager.outil')->delete( $outils );

        //inform user connected
        $this->get('session')->getFlashBag()->add('info', 'Supression effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_outil') );
    }
}