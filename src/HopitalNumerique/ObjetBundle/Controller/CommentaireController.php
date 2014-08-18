<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Commentaire controller.
 */
class CommentaireController extends Controller
{
    /**
     * Affiche la liste des Commentaire.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_objet.grid.commentaire');

        return $grid->render('HopitalNumeriqueObjetBundle:Commentaire:index.html.twig');
    }

    /**
     * Affiche le formulaire d'édition de Commentaire.
     *
     * @param integer $id Id de Commentaire.
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $commentaire = $this->get('hopitalnumerique_objet.manager.commentaire')->findOneBy( array('id' => $id) );

        return $this->renderForm('hopitalnumerique_objet_commentaire', $commentaire, 'HopitalNumeriqueObjetBundle:Commentaire:edit.html.twig' );
    }

    /**
     * Suppresion d'un Commentaire.
     * 
     * @param integer $id Id de Commentaire.
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $commentaire = $this->get('hopitalnumerique_objet.manager.commentaire')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('hopitalnumerique_objet.manager.commentaire')->delete( $commentaire );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_objet_admin_commentaire').'"}', 200);
    }

    /**
     * Publication/déplication d'un Commentaire.
     * 
     * @param integer $id Id de Commentaire.
     * METHOD = POST|DELETE
     */
    public function togglePublicationAction( $id )
    {
        $commentaire = $this->get('hopitalnumerique_objet.manager.commentaire')->findOneBy( array( 'id' => $id) );

        $publication = !$commentaire->getPublier();

        $commentaire->setPublier($publication);

        //Suppression de l'entitée
        $this->get('hopitalnumerique_objet.manager.commentaire')->save( $commentaire );

        $this->get('session')->getFlashBag()->add('info', ($publication ? 'Le commentaire est maintenant publié.' : 'Le commentaire n\'est plus publié.') );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_objet_admin_commentaire').'"}', 200);
    }





    /**
     * Effectue le render du formulaire Commentaire.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param Commentaire   $entity   Entité $commentaire
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $commentaire, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $commentaire);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($commentaire->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_objet.manager.commentaire')->save($commentaire);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Commentaire ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_objet_admin_commentaire') : $this->generateUrl('hopitalnumerique_objet_admin_commentaire_edit', array( 'id' => $commentaire->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form'        => $form->createView(),
            'commentaire' => $commentaire
        ));
    }
}