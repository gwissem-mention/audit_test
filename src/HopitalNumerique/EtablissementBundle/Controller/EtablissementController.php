<?php
namespace HopitalNumerique\EtablissementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Etablissement controller.
 */
class EtablissementController extends Controller
{
    /**
     * Lists all Etablissement entities.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_etablissement.grid.etablissement');

        return $grid->render('HopitalNumeriqueEtablissementBundle:Etablissement:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout d'établissement
     */
    public function addAction()
    {
        $etablissement = $this->get('hopitalnumerique_etablissement.manager.etablissement')->createEmpty();

        return $this->renderForm('hopitalnumerique_etablissement_etablissement', $etablissement, 'HopitalNumeriqueEtablissementBundle:Etablissement:edit.html.twig' );
    }
    
    /**
     * Affichage du formulaire d'etablissement
     * 
     * @param intger $id Identifiant de l'etablissement
     */
    public function editAction( $id )
    {
        //Récupération de l'etablissement passé en param
        $etablissement = $this->get('hopitalnumerique_etablissement.manager.etablissement')->findOneBy( array('id' => $id) );

        return $this->renderForm('hopitalnumerique_etablissement_etablissement', $etablissement, 'HopitalNumeriqueEtablissementBundle:Etablissement:edit.html.twig' );
    }

    /**
     * Affichage de la fiche d'un etablissement
     * 
     * @param integer $id ID de l'etablissement
     */
    public function showAction( $id )
    {
        //Récupération de l'etablissement passé en param
        $etablissement = $this->get('hopitalnumerique_etablissement.manager.etablissement')->findOneBy( array('id' => $id) );

        return $this->render('HopitalNumeriqueEtablissementBundle:Etablissement:show.html.twig', array(
            'etablissement' => $etablissement
        ));
    }

    /**
     * Suppression d'un etablissement
     *
     * @param integer $id ID de l'etablissement
     */
    public function deleteAction( $id )
    {
        $etablissement = $this->get('hopitalnumerique_etablissement.manager.etablissement')->findOneBy( array('id' => $id) );
        
        //Tentative de suppression si l'établissement n'est lié nul part
        try
        {
            //Suppression de l'etablissement
            $this->get('hopitalnumerique_etablissement.manager.etablissement')->delete( $etablissement );
            $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );
        } 
        catch (\Exception $e)
        {
            $this->get('session')->getFlashBag()->add('danger', 'Suppression impossible, la référence est actuellement liée et ne peut pas être supprimée.');
        }

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_etablissement').'"}', 200);
    }

    /**
     * Génère la liste des département en fonction de l'id de la région
     */
    public function departementsAction()
    {
        $id           = $this->get('request')->request->get('id');
        $departements = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array('parent' => $id) );

        return $this->render('HopitalNumeriqueEtablissementBundle:Etablissement:departements.html.twig', array(
            'departements' => $departements
        ));
    }

    /**
     * Gestion du grid des établissements de type 'Autre'
     */
    public function autresAction()
    {
        $grid = $this->get('hopitalnumerique_user.grid.etablissement');

        return $grid->render('HopitalNumeriqueEtablissementBundle:Etablissement:autres.html.twig');
    }

    /**
     * Passe l'user à "archivé".
     */
    public function archiverAction( $id )
    {
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $id) );
        $user->setArchiver( !$user->getArchiver() );
        $this->get('hopitalnumerique_user.manager.user')->save( $user );

        $this->get('session')->getFlashBag()->add('info', 'L\'utilisateur ' . ($user->getArchiver() ? ' est archivé.' : 'n\' est plus archivé.') );

        return $this->redirect( $this->generateUrl('hopitalnumerique_etablissement_autres') );
    }

















    /**
     * Effectue le render du formulaire Etablissement
     *
     * @param string        $formName        Nom du service associé au formulaire
     * @param Etablissement $etablissement   Entité Etablissement
     * @param string        $view            Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $etablissement, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $etablissement);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($etablissement->getId());

                // On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_etablissement.manager.etablissement')->save($etablissement);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Etablissement ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_etablissement') : $this->generateUrl('hopitalnumerique_etablissement_edit', array( 'id' => $etablissement->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form'          => $form->createView(),
            'etablissement' => $etablissement
        ));
    }
}