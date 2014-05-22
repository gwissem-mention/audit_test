<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resultat controller.
 */
class ResultatController extends Controller
{
    /**
     * Affiche la liste des Resultats.
     */
    public function indexAction(Outil $outil)
    {
        $grid = $this->get('hopitalnumerique_autodiag.grid.resultat');
        $grid->setSourceCondition('outil', $outil->getId() );

        return $grid->render('HopitalNumeriqueAutodiagBundle:Resultat:index.html.twig', array('outil'=>$outil));
    }


    // /**
    //  * Affiche le formulaire d'édition de Categorie.
    //  *
    //  * @param integer $id Id de Categorie.
    //  */
    // public function editAction( $id, $idCat, Request $request )
    // {
    //     //Récupération de l'entité passée en paramètre
    //     $categorie = $this->get('hopitalnumerique_autodiag.manager.categorie')->findOneBy( array('id' => $idCat) );

    //     $form = $this->createForm( 'hopitalnumerique_autodiag_categorie', $categorie);

    //     if ( $form->handleRequest($request)->isValid() ) {
    //         //On utilise notre Manager pour gérer la sauvegarde de l'objet
    //         $this->get('hopitalnumerique_autodiag.manager.categorie')->save($categorie);
            
    //         // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
    //         $this->get('session')->getFlashBag()->add( 'info', 'Categorie mise à jour.' );
            
    //         return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_categorie', array( 'id' => $categorie->getOutil()->getId() ) ) );
    //     }

    //     return $this->render( 'HopitalNumeriqueAutodiagBundle:Categorie:edit.html.twig' , array(
    //         'form'      => $form->createView(),
    //         'categorie' => $categorie,
    //         'outil'     => $categorie->getOutil()
    //     ));
    // }
}