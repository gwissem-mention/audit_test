<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Chapitre;
use HopitalNumerique\AutodiagBundle\Entity\Outil;
use Nodevo\ToolsBundle\Tools\Chaine;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chapitre controller.
 */
class ChapitreController extends Controller
{
    /**
     * Affiche la liste des chapitres.
     */
    public function indexAction(Outil $outil)
    {
        //get ponderations
        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees($outil->getDomainesId());
        
        //get chapitres
        $chapitres = $this->get('hopitalnumerique_autodiag.manager.chapitre')->getArbo( $outil, $refsPonderees );

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Chapitre:index.html.twig' , array(
            'outil'     => $outil,
            'chapitres' => $chapitres
        ));
    }

    /**
     * Affiche la liste des chapitres.
     */
    public function listeAction(Outil $outil)
    {
        $chapitres = $this->get('hopitalnumerique_autodiag.manager.chapitre')->getChapitresForListe( $outil );

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Chapitre:liste.html.twig' , array(
            'outil'     => $outil,
            'chapitres' => $chapitres
        ));
    }

    /**
     * Ajoute un chapitre
     */
    public function addAction(Outil $outil, Request $request)
    {
        //créer un chapitre
        $chapitre = $this->get('hopitalnumerique_autodiag.manager.chapitre')->createEmpty();
        $chapitre->setOutil( $outil );

        //guess order
        $order = $this->get('hopitalnumerique_autodiag.manager.chapitre')->countChapitres($outil) + 1;
        $chapitre->setOrder( $order );

        //init datas
        $titre = trim($request->request->get('titre')) ? : 'Chapitre '.$order;
        $tool  = new Chaine( $titre );
        $chapitre->setTitle( $titre );
        $chapitre->setAlias( $tool->minifie() );
        $chapitre->setCode( 0 );

        //save
        $this->get('hopitalnumerique_autodiag.manager.chapitre')->save( $chapitre );

        return $this->render('HopitalNumeriqueAutodiagBundle:Chapitre:add.html.twig', array(
            'chapitre' => $chapitre
        ));
    }

    /**
     * Met à jour l'ordre des différents chapitres
     */
    public function reorderAction(Outil $outil)
    {
        //get datas serialzed
        $datas = $this->get('request')->request->get('datas');

        //execute reorder
        $this->get('hopitalnumerique_autodiag.manager.chapitre')->reorder( $datas, null );
        $this->getDoctrine()->getManager()->flush();

        //return success.true si le fichier existe deja
        return new Response('{"success":true}', 200);
    }

    /**
     * Edition du lien d'un chapitre.
     *
     */
    public function editLienAction( Chapitre $chapitre )
    {
        $texte = $this->get('request')->request->get('texte');
        $chapitre->setLien($texte);
        
        //Sauvegarde
        $this->get('hopitalnumerique_autodiag.manager.chapitre')->save( $chapitre );

        return new Response('{"success":true}', 200);
    }

    /**
     * Recupère le lien d'un chapitre.
     */
    public function getLienAction( $id )
    {
        $chapitre = $this->get('hopitalnumerique_autodiag.manager.chapitre')->findOneBy( array('id' => $id) );
        $lien = is_null($chapitre->getLien()) ? '' : $chapitre->getLien();

        return new Response('{"success":true, "lien":"'. $lien .'"}', 200);
    }

    /**
     * Suppresion d'un chapitre.
     *
     * METHOD = POST|DELETE
     */
    public function deleteAction( Chapitre $chapitre )
    {
        //On récupère le parent de l'élément que l'on delete.
        $parent = $chapitre->getParent();

        //delete
        $this->get('hopitalnumerique_autodiag.manager.chapitre')->delete( $chapitre );

        //On recherche si le parent de l'élément que l'on delete à encore des enfants après cette supression
        $stillHaveChilds = 0;
        if( !is_null($parent) ){
            $childs = $this->get('hopitalnumerique_autodiag.manager.chapitre')->findBy( array( 'parent' => $parent ) );
            if( !empty($childs ) )
                $stillHaveChilds = 1;
        }

        return new Response('{"success":true, "childs":'.$stillHaveChilds.'}', 200);
    }

    /**
     * POPIN : Edite le contenu d'un chapitre
     */
    public function editAction(Chapitre $chapitre)
    {
        $form = $this->createForm( 'hopitalnumerique_autodiag_chapitre', $chapitre);

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Chapitre:edit.html.twig' , array(
            'form'     => $form->createView(),
            'chapitre' => $chapitre
        ));
    }

    /**
     * Sauvegarde AJAX du chapitre
     */
    public function saveAction(Chapitre $chapitre, Request $request)
    {
        $form = $this->createForm( 'hopitalnumerique_autodiag_chapitre', $chapitre);

        if ( $form->handleRequest($request)->isValid() ) {

            //handle Alias
            $tool = new Chaine( ( $chapitre->getAlias() == '' ? $chapitre->getTitle() : $chapitre->getAlias() ) . ' ' . $chapitre->getId());
            $chapitre->setAlias( $tool->minifie() );

            //Récupère tout les chapitres enfants de ce chapitre
            $chapitreChildren = $this->get('hopitalnumerique_autodiag.manager.chapitre')->findBy(array('parent' => $chapitre->getId()));
            foreach ($chapitreChildren as $chapitreChild) 
            {
                $chapitreChild->setAffichageRestitutionBarre($chapitre->getAffichageRestitutionBarre());
                $chapitreChild->setAffichageRestitutionRadar($chapitre->getAffichageRestitutionRadar());
                $chapitreChild->setAffichageRestitutionTableau($chapitre->getAffichageRestitutionTableau());
            }

            //save
            $this->getDoctrine()->getManager()->flush();
            
            return new Response('{"success":true}', 200);
        }

        return new Response('{"success":false}', 200);
    }
}
