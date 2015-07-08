<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \Nodevo\ToolsBundle\Tools\Chaine;
use Doctrine\Common\Cache\ApcCache;


/**
 * Contenu controller.
 */
class ContenuController extends Controller
{
    /**
     * Ajoute un élément dans le sommaire
     */
    public function addAction($id)
    {
        //récupère l'objet courant
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );

        //créer un contenu vide et on ajoute l'objet
        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->createEmpty();
        $contenu->setObjet( $objet );

        //guess order
        $order = $this->get('hopitalnumerique_objet.manager.contenu')->countContenu($objet) + 1;
        $contenu->setOrder( $order );

        //save contenu
        $this->get('hopitalnumerique_objet.manager.contenu')->save($contenu);

        $contenu->setAlias( $contenu->getId() . '_contenu' );
        $this->get('hopitalnumerique_objet.manager.contenu')->save($contenu);

        //set objet as infra doc
        if( !$objet->isInfraDoc() ){
            $objet->setInfraDoc( true );
            $this->get('hopitalnumerique_objet.manager.objet')->save( $objet );
        }

        return $this->render('HopitalNumeriqueObjetBundle:Contenu:add.html.twig', array(
            'id'    => $contenu->getId(),
            'order' => $order
        ));
    }

    /**
     * Affiche le formulaire d'édition de Contenu.
     */
    public function editAction( $id )
    {   
        //Récupération de l'entité passée en paramètre
        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array('id' => $id) );
        
        return $this->renderForm('hopitalnumerique_objet_contenu', $contenu, 'HopitalNumeriqueObjetBundle:Contenu:edit.html.twig' );
    }

    /**
     * Affiche le formulaire d'édition de Contenu.
     */
    public function formAction( $id )
    {   
        //Récupération de l'entité passée en paramètre
        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array('id' => $id) );
        
        return $this->render('HopitalNumeriqueObjetBundle:Contenu:form.html.twig', array(
            'contenu' => $contenu
        ));
    }

    /**
     * Suppresion d'un Contenu.
     *
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array( 'id' => $id) );

        //On récupère le parent de l'élément que l'on delete.
        $parent = $contenu->getParent();

        //get objet
        $objet = $contenu->getObjet();
        
        //delete
        $this->get('hopitalnumerique_objet.manager.contenu')->delete( $contenu );

        //find nb contenu left for this objet
        $contenus = $this->get('hopitalnumerique_objet.manager.contenu')->findBy( array( 'objet' => $objet->getId() ) );

        //si aucun contenus, on met l'infradoc à false
        if( empty($contenus) ) {
            $objet->setInfraDoc( false );
            $this->get('hopitalnumerique_objet.manager.objet')->save( $objet );
        }

        //On recherche si le parent de l'élément que l'on delete à encore des enfants après cette supression
        $stillHaveChilds = 0;
        if( !is_null($parent) ){
            $childs = $this->get('hopitalnumerique_objet.manager.contenu')->findBy( array( 'parent' => $parent ) );
            if( !empty($childs ) )
                $stillHaveChilds = 1;
        }

        return new Response('{"success":true, "childs":'.$stillHaveChilds.'}', 200);
    }

    /**
     * Reorder les contenus
     *
     * @return Response
     */
    public function reorderAction()
    {
        //get datas serialzed
        $datas = $this->get('request')->request->get('datas');
        $this->reorderElements( $datas, null );

        //return success.true si le fichier existe deja
        return new Response('{"success":true}', 200);
    }

    /**
     * Fancybox d'upload de CSV
     *
     * @return Response
     */
    public function uploadAction()
    {
        return $this->render('HopitalNumeriqueObjetBundle:Contenu:upload.html.twig');
    }

    /**
     * Parse le fichier CSV uploadé
     *
     * @return Response
     */
    public function uploadParseAction($id)
    {
        //récupère l'objet courant
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );

        //get datas
        $csv    = $this->get('request')->request->get('csv');
        $result = $this->get('hopitalnumerique_objet.manager.contenu')->parseCsv( $csv, $objet );
        
        if($result) {
            if( !$objet->isInfraDoc() ){
                $objet->setInfraDoc( true );
                $this->get('hopitalnumerique_objet.manager.objet')->save( $objet );
            }

            $this->get('session')->getFlashBag()->add( 'success' , 'Sommaire importé avec succès.' ); 
        }else
            $this->get('session')->getFlashBag()->add( 'danger' , 'Le format du CSV importé n\'était pas correct, merci de réessayer.' ); 
                
        // On redirige vers la page objet
        return new Response('{"success":true, "url" : "'.$this->generateUrl( 'hopitalnumerique_objet_objet_edit', array( 'id' => $id ) ).'"}', 200);
    }










    /**
     * Tri de manière récursive les éléments de contenu
     *
     * @param array        $elements Liste des éléments
     * @param Contenu|null $parent   Parent ou null
     *
     * @return empty
     */
    private function reorderElements( $elements, $parent )
    {
        $order = 1;

        foreach($elements as $element) {
            $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array('id' => $element['id']) );
            $contenu->setOrder( $order );
            $contenu->setParent( $parent );

            $this->get('hopitalnumerique_objet.manager.contenu')->save( $contenu );
            $order++;

            if( isset($element['children']) )
                $this->reorderElements( $element['children'], $contenu );
        }
    }

    /**
     * Effectue le render du formulaire Contenu.
     *
     * @param string  $formName Nom du formulaire
     * @param Contenu $contenu  Objet Contenu
     * @param string  $view     Template de rendu
     *
     * @return Response
     */
    private function renderForm( $formName, $contenu, $view )
    {
        //Création du formulaire via le service
        $form = $this->createForm( $formName, $contenu);

        $request = $this->get('request');
        
        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            //get form Datas
            $titre   = $request->request->get('titre');
            $alias   = $request->request->get('alias');
            $content = $request->request->get('contenu');
            $notify  = $request->request->get('notify');

            //error si le titre est vide
            if($titre == '')
                return new Response('{"success":false,"titre":true,"alias":false}', 200);

            //set Form datas
            $contenu->setTitre( $titre );
            $contenu->setContenu( $content );

            if( $notify === "1")
                $contenu->setDateModification( new \DateTime() );

            //on régénère l'alias à chaque fois
            $tool = ( $alias == '' || $alias == 'nouveau-contenu' ) ? new Chaine( $titre ) : new Chaine( $alias );
            $contenu->setAlias( $tool->minifie() );

            //check if alias exist in this object
            if( $this->get('hopitalnumerique_objet.manager.contenu')->countAlias( $contenu, $alias ) >= 1 )
                return new Response('{"success":false,"titre":false,"alias":true}', 200);

            //save
            $this->get('hopitalnumerique_objet.manager.contenu')->save($contenu);

            //Destruction du cache APC concernant le contenu
            $cacheDriver = new ApcCache();
            $cacheName   = "_publication_contenu_" . $contenu->getId();
            $cacheDriver->delete($cacheName);
            
            //reload glossaire stuff
            $this->get('hopitalnumerique_glossaire.manager.glossaire')->parsePublications( array(), array($contenu) );
            $this->getDoctrine()->getManager()->flush();

            return new Response('{"success":true, "titre" : "'.$titre.'"}', 200);
        }

        return $this->render( $view , array(
            'form'    => $form->createView(),
            'contenu' => $contenu
        ));
    }
}