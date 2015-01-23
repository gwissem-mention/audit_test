<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Categorie;
use HopitalNumerique\AutodiagBundle\Entity\Outil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Categorie controller.
 */
class CategorieController extends Controller
{
    /**
     * Affiche la liste des Categorie.
     */
    public function indexAction(Outil $outil)
    {
        $grid = $this->get('hopitalnumerique_autodiag.grid.categorie');
        $grid->setSourceCondition('outil', $outil->getId() );

        return $grid->render('HopitalNumeriqueAutodiagBundle:Categorie:index.html.twig', array('outil'=>$outil));
    }

    /**
     * Affiche le formulaire d'ajout de Categorie.
     */
    public function addAction(Outil $outil, Request $request)
    {
        $categorie = $this->get('hopitalnumerique_autodiag.manager.categorie')->createEmpty();
        $categorie->setOutil( $outil );

        $form = $this->createForm( 'hopitalnumerique_autodiag_categorie', $categorie);

        if ( $form->handleRequest($request)->isValid() ) {
            //On utilise notre Manager pour gérer la sauvegarde de l'objet
            $this->get('hopitalnumerique_autodiag.manager.categorie')->save($categorie);
            
            // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
            $this->get('session')->getFlashBag()->add( 'success', 'Categorie ajoutée.' );
            
            return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_categorie', array( 'id' => $outil->getId() ) ) );
        }

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Categorie:edit.html.twig' , array(
            'form'      => $form->createView(),
            'categorie' => $categorie,
            'outil'     => $outil
        ));
    }

    /**
     * Affiche le formulaire d'édition de Categorie.
     *
     * @param integer $id Id de Categorie.
     */
    public function editAction( $id, $idCat, Request $request )
    {
        //Récupération de l'entité passée en paramètre
        $categorie = $this->get('hopitalnumerique_autodiag.manager.categorie')->findOneBy( array('id' => $idCat) );

        $form = $this->createForm( 'hopitalnumerique_autodiag_categorie', $categorie);

        if ( $form->handleRequest($request)->isValid() ) {
            //On utilise notre Manager pour gérer la sauvegarde de l'objet
            $this->get('hopitalnumerique_autodiag.manager.categorie')->save($categorie);
            
            // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
            $this->get('session')->getFlashBag()->add( 'info', 'Categorie mise à jour.' );
            
            return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_categorie', array( 'id' => $categorie->getOutil()->getId() ) ) );
        }

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Categorie:edit.html.twig' , array(
            'form'      => $form->createView(),
            'categorie' => $categorie,
            'outil'     => $categorie->getOutil()
        ));
    }

    /**
     * Suppresion d'un Categorie.
     * 
     * @param integer $id Id de Categorie.
     * METHOD = POST|DELETE
     */
    public function deleteAction( Categorie $categorie )
    {
        $idOutil   = $categorie->getOutil()->getId();
        $questions = $this->get('hopitalnumerique_autodiag.manager.question')->findBy( array('categorie' => $categorie ) );

        if( count($questions) >= 1 ){
            $this->get('session')->getFlashBag()->add('danger', 'Il est impossible de supprimer une catégorie qui possède des questions.' );
        }else{
            //Suppression de l'entitée
            $this->get('hopitalnumerique_autodiag.manager.categorie')->delete( $categorie );
            $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );
        }

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_autodiag_categorie', array('id'=>$idOutil)).'"}', 200);
    }

    /**
     * Suppression de masse des categories
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function deleteMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_autodiag.grid.categorie')->getRawData();
            foreach($rawDatas as $data)
            {
                $primaryKeys[] = $data['idCat'];
            }
        }        

        $categories = $this->get('hopitalnumerique_autodiag.manager.categorie')->findBy( array('id' => $primaryKeys) );

        if(count($categories) > 0 )
        {
            $idOutil = array_values($categories)[0]->getOutil()->getId();
            $url     = $this->generateUrl('hopitalnumerique_autodiag_categorie', array('id'=>$idOutil));
        }
        else
        {
            $url = $this->generateUrl('hopitalnumerique_autodiag_outil');;
        }

        $this->get('hopitalnumerique_autodiag.manager.categorie')->delete( $categories );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return $this->redirect( $url );
    }
}