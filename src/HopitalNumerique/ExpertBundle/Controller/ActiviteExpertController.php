<?php

namespace HopitalNumerique\ExpertBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\ExpertBundle\Entity\ActiviteExpert;

/**
 * ActiviteExpert controller.
 */
class ActiviteExpertController extends Controller
{
    /**
     * Affiche la liste des ActiviteExpert.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_expert.grid.activiteexpert');

        return $grid->render('HopitalNumeriqueExpertBundle:ActiviteExpert:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de ActiviteExpert.
     */
    public function addAction()
    {
        $activiteexpert = $this->get('hopitalnumerique_expert.manager.activiteexpert')->createEmpty();

        return $this->renderForm('hopitalnumerique_expert_activiteexpert', $activiteexpert, 'HopitalNumeriqueExpertBundle:ActiviteExpert:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de ActiviteExpert.
     *
     * @param integer $id Id de ActiviteExpert.
     */
    public function editAction( ActiviteExpert $activiteExpert )
    {
        return $this->renderForm('hopitalnumerique_expert_activiteexpert', $activiteExpert, 'HopitalNumeriqueExpertBundle:ActiviteExpert:edit.html.twig' );
    }

    /**
     * Affiche le ActiviteExpert en fonction de son ID passé en paramètre.
     * 
     * @param integer $id Id de ActiviteExpert.
     */
    public function showAction( ActiviteExpert $activiteExpert )
    {
        return $this->render('HopitalNumeriqueExpertBundle:ActiviteExpert:show.html.twig', array(
            'activiteExpert' => $activiteExpert,
        ));
    }

    /**
     * Suppresion d'un ActiviteExpert.
     * 
     * @param integer $id Id de ActiviteExpert.
     * METHOD = POST|DELETE
     */
    public function deleteAction( ActiviteExpert $activiteExpert )
    {
        //Suppression de l'entitée
        $this->get('hopitalnumerique_expert.manager.activiteexpert')->delete( $activiteExpert );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_expert_expert_activite').'"}', 200);
    }
    
    /**
     * POPIN : Partage de resultat
     */
    public function parametrageAction()
    {
        $montantVacation = $this->get('hopitalnumerique_reference.manager.reference')->findOneById(560);

        return $this->render( 'HopitalNumeriqueExpertBundle:ActiviteExpert:fancy.html.twig' , array(
            'montantVacation' => $montantVacation
        ));
    }

    /**
     * [payerFactureAction description]
     *
     * @param ActiviteExpert $activiteExpert [description]
     *
     * @return [type]
     */
    public function payerFactureAction(ActiviteExpert $activiteExpert)
    {
        $activiteExpert->setEtatValidation(true);

        $this->get('hopitalnumerique_expert.manager.activiteexpert')->save($activiteExpert);

        $this->get('session')->getFlashBag()->add( 'info' , 'Facture payées.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_expert_expert_activite') );
    }

    /**
     * Action Annuler, on dévérouille l'objet et on redirige vers l'index
     */
    public function cancelAction( $id, $message )
    {
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );

        //On récupère l'user connecté et son role
        $user  = $this->get('security.context')->getToken()->getUser();
        
        //si l'user connecté est propriétaire de l'objet ou si l'user est admin : unlock autorisé
        if( $user->hasRole('ROLE_ADMINISTRATEUR_1') || $objet->getLockedBy() == $user ) {
            $this->get('hopitalnumerique_objet.manager.objet')->unlock($objet);

            //si on à appellé l'action depuis le button du grid, on met un message à l'user, sinon pas besoin de message
            if( !is_null($message ) )
                $this->get('session')->getFlashBag()->add( 'info' , 'Objet dévérouillé.' );
        }else
            $this->get('session')->getFlashBag()->add( 'danger' , 'Vous n\'avez pas l\'autorisation de déverrouiller cet objet.' );
        
        return $this->redirect( $this->generateUrl('hopitalnumerique_objet_objet') );
    }





    /**
     * Effectue le render du formulaire ActiviteExpert.
     *
     * @param string $formName Nom du service associé au formulaire
     * @param ActiviteExpert   $entity   Entité $activiteexpert
     * @param string $view     Chemin de la vue ou sera rendu le formulaire
     *
     * @return Form | redirect
     */
    private function renderForm( $formName, $activiteexpert, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $activiteexpert);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            
            // On bind les données du form
            $form->handleRequest($request);

            $experts = $form->get('expertConcernes')->getData();
            $anapiens = $form->get('anapiens')->getData();

            if(!is_null($experts) && count($experts) > 0 
                && !is_null($anapiens) && count($anapiens) > 0)
            {
                //si le formulaire est valide
                if ($form->isValid()) {
                    //test ajout ou edition
                    $new = is_null($activiteexpert->getId());

                    //On utilise notre Manager pour gérer la sauvegarde de l'objet
                    $this->get('hopitalnumerique_expert.manager.activiteexpert')->save($activiteexpert);
                    
                    // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                    $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Une activite expert ' . ($new ? 'ajouté.' : 'mis à jour.') ); 
                    
                    //on redirige vers la page index ou la page edit selon le bouton utilisé
                    $do = $request->request->get('do');
                    return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_expert_expert_activite') : $this->generateUrl('hopitalnumerique_expert_expert_activite_edit', array( 'id' => $activiteexpert->getId() ) ) ) );
                }
            }
            else
            {
                $message = (!is_null($experts) && count($experts) > 0) ? 'experts concernés' : 'anapiens';
                $this->get('session')->getFlashBag()->add('danger', 'Attention la liste '.$message.' ne peut pas être vide.' );
            }
        }

        return $this->render( $view , array(
            'form'           => $form->createView(),
            'activiteexpert' => $activiteexpert
        ));
    }
}