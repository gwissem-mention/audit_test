<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * ExpBesoinGestion controller.
 */
class ExpBesoinGestionController extends Controller
{
    /**
     * Affiche la liste des ExpBesoinGestion.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_recherche.grid.expbesoingestion');

        return $grid->render('HopitalNumeriqueRechercheBundle:ExpBesoinGestion:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de ExpBesoinGestion.
     */
    public function addAction()
    {
        $expbesoingestion = $this->get('hopitalnumerique_recherche.manager.expbesoingestion')->createEmpty();

        return $this->renderForm('hopitalnumerique_recherche_expbesoingestion', $expbesoingestion, 'HopitalNumeriqueRechercheBundle:ExpBesoinGestion:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de ExpBesoinGestion.
     *
     * @param integer $id Id de ExpBesoinGestion.
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $expbesoingestion = $this->get('hopitalnumerique_recherche.manager.expbesoingestion')->findOneBy( array('id' => $id) );

        return $this->renderForm('hopitalnumerique_recherche_expbesoingestion', $expbesoingestion, 'HopitalNumeriqueRechercheBundle:ExpBesoinGestion:edit.html.twig' );
    }

    /**
     * Affiche le ExpBesoinGestion en fonction de son ID passé en paramètre.
     * 
     * @param integer $id Id de ExpBesoinGestion.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $expbesoingestion = $this->get('hopitalnumerique_recherche.manager.expbesoingestion')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueRechercheBundle:ExpBesoinGestion:show.html.twig', array(
            'expbesoingestion' => $expbesoingestion,
        ));
    }

    /**
     * Suppresion d'un ExpBesoinGestion.
     * 
     * @param integer $id Id de ExpBesoinGestion.
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $expbesoingestion = $this->get('hopitalnumerique_recherche.manager.expbesoingestion')->findOneBy( array( 'id' => $id) );

        //Suppression de l'entitée
        $this->get('hopitalnumerique_recherche.manager.expbesoingestion')->delete( $expbesoingestion );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_recherche_admin_aide-expression-besoin_gestion').'"}', 200);
    }





    /**
     * Effectue le render du formulaire ExpBesoinGestion.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param ExpBesoinGestion   $entity   Entité $expbesoingestion
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $expbesoingestion, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $expbesoingestion);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            //si le formulaire est valide
            if ($form->isValid()) {
                //test ajout ou edition
                $new = is_null($expbesoingestion->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_recherche.manager.expbesoingestion')->save($expbesoingestion);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'ExpBesoinGestion ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                
                //on redirige vers la page index ou la page edit selon le bouton utilisé
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_recherche_admin_aide-expression-besoin_gestion') : $this->generateUrl('hopitalnumerique_recherche_admin_aide-expression-besoin_gestion_edit', array( 'id' => $expbesoingestion->getId() ) ) ) );
            }
        }

        return $this->render( $view , array(
            'form'             => $form->createView(),
            'expbesoingestion' => $expbesoingestion
        ));
    }
}