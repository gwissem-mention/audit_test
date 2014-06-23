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
        $chapitres = $this->get('hopitalnumerique_autodiag.manager.chapitre')->getArbo( $outil );

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
            $tool = new Chaine( ( $chapitre->getAlias() == '' ? $chapitre->getTitle() : $chapitre->getAlias() ) );
            $chapitre->setAlias( $tool->minifie() );

            //save
            $this->getDoctrine()->getManager()->flush();
            
            return new Response('{"success":true}', 200);
        }

        return new Response('{"success":false}', 200);
    }

    /**
     * AJAX : check si l'alias est unique
     */
    public function checkAliasUniqueAction(Chapitre $chapitre, Request $request)
    {
        $alias         = $request->request->get('alias');
        $checkChapitre = $this->get('hopitalnumerique_autodiag.manager.chapitre')->findBy( array('alias'=>$alias, 'outil'=>$chapitre->getOutil()) );
        if ( count($checkChapitre) > 1)
            return new Response('{"success":false}', 200);
        elseif ( count($checkChapitre) == 0)
            return new Response('{"success":true}', 200);

        $checkChapitre = $checkChapitre[0];
        //si on a trouvé un chapitre et que l'ID est n'est pas celui en cours d'édition, l'alias existe déjà
        if( $checkChapitre && $checkChapitre->getId() != $chapitre->getId() )
            return new Response('{"success":false}', 200);
        else
            return new Response('{"success":true}', 200);
    }














}