<?php

namespace HopitalNumerique\RechercheParcoursBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * RechercheParcoursGestion controller.
 */
class RechercheParcoursGestionController extends Controller
{
    /**
     * Affiche la liste des RechercheParcoursGestion.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_rechercheparcours.grid.rechercheparcoursgestion');

        return $grid->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursGestion:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de RechercheParcoursGestion.
     */
    public function addAction()
    {
        $rechercheparcoursgestion = $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->createEmpty();

        return $this->renderForm('hopitalnumerique_rechercheparcours_rechercheparcoursgestion', $rechercheparcoursgestion, 'HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursGestion:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de RechercheParcoursGestion.
     *
     * @param integer $id Id de RechercheParcoursGestion.
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $rechercheparcoursgestion = $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->findOneBy( array('id' => $id) );

        return $this->renderForm('hopitalnumerique_rechercheparcours_rechercheparcoursgestion', $rechercheparcoursgestion, 'HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursGestion:edit.html.twig' );
    }

    /**
     * Affiche le RechercheParcoursGestion en fonction de son ID passé en paramètre.
     * 
     * @param integer $id Id de RechercheParcoursGestion.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $rechercheparcoursgestion = $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueRechercheParcoursBundle:RechercheParcoursGestion:show.html.twig', array(
            'rechercheparcoursgestion' => $rechercheparcoursgestion,
        ));
    }

    /**
     * Suppresion d'un RechercheParcoursGestion.
     * 
     * @param integer $id Id de RechercheParcoursGestion.
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $rechercheparcoursgestion = $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->delete( $rechercheparcoursgestion );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion').'"}', 200);
    }





    /**
     * Effectue le render du formulaire RechercheParcoursGestion.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param RechercheParcoursGestion   $entity   Entité $rechercheparcoursgestion
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $rechercheparcoursgestion, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $rechercheparcoursgestion);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($rechercheparcoursgestion->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_rechercheparcours.manager.rechercheparcoursgestion')->save($rechercheparcoursgestion);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'RechercheParcoursGestion ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion') : $this->generateUrl('hopitalnumerique_rechercheparcours_admin_recherche-par-parcours_gestion_edit', array( 'id' => $rechercheparcoursgestion->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form'             => $form->createView(),
            'rechercheparcoursgestion' => $rechercheparcoursgestion
        ));
    }
}