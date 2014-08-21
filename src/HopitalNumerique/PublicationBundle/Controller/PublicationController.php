<?php

namespace HopitalNumerique\PublicationBundle\Controller;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PublicationController extends Controller
{
    /**
     * Objet Action
     */
    public function objetAction(Objet $objet)
    {
        //objet visualisation
        $objet->setNbVue( ($objet->getNbVue() + 1) );

        //Si l'user connecté à le rôle requis pour voir l'objet
        if( $this->checkAuthorization( $objet ) === false ){
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );
        }
        
        //Types objet
        $types = $this->get('hopitalnumerique_objet.manager.objet')->formatteTypes( $objet->getTypes() );

        //get Contenus : for sommaire
        $contenus = $objet->isInfraDoc() ? $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjet( $objet->getId() ) : array();

        //set Consultation entry
        $this->get('hopitalnumerique_objet.manager.consultation')->consulted( $objet );

        //build productions with authorizations
        $productions = $this->getProductionsAssocies($objet->getObjets());

        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();
        //Récupération de la note pour l'objet de l'utilisateur courant
        $note = $this->get('hopitalnumerique_objet.manager.note')->findOneBy(array('user' => $user, 'objet' => $objet));


        //render
        return $this->render('HopitalNumeriquePublicationBundle:Publication:objet.html.twig', array(
            'objet'        => $objet,
            'objets'       => $this->getObjetsFromRecherche( $objet ),
            'note'         => $note,
            'types'        => $types,
            'contenus'     => $contenus,
            'productions'  => $productions,
            'meta'         => $this->get('hopitalnumerique_recherche.manager.search')->getMetas($objet->getReferences(), $objet->getResume() ),
            'ambassadeurs' => $this->getAmbassadeursConcernes( $objet->getId() )
        ));
    }

    /**
     * Contenu Action
     */
    public function contenuAction($id, $alias, $idc, $aliasc)
    {
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $id ) );

        //Si l'user connecté à le rôle requis pour voir l'objet
        if( $this->checkAuthorization( $objet ) === false )
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );

        //on récupère le contenu
        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array( 'id' => $idc ) );
        $prefix  = $this->get('hopitalnumerique_objet.manager.contenu')->getPrefix($contenu);

        //add visualisation
        $contenu->setNbVue( ($contenu->getNbVue() + 1) );

        //Types objet
        $types = $this->get('hopitalnumerique_objet.manager.objet')->formatteTypes( $objet->getTypes() );

        //get Contenus : for sommaire
        $contenus = $objet->isInfraDoc() ? $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjet( $id ) : array();

        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();
        //Récupération de la note pour l'objet de l'utilisateur courant
        $note = $this->get('hopitalnumerique_objet.manager.note')->findOneBy(array('user' => $user, 'contenu' => $contenu));

        //set Consultation entry
        $this->get('hopitalnumerique_objet.manager.consultation')->consulted( $contenu, true );

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Publication:objet.html.twig', array(
            'objet'        => $objet,
            'objets'       => $this->getObjetsFromRecherche( $contenu ),
            'note'         => $note,
            'contenus'     => $contenus,
            'types'        => $types,
            'contenu'      => $contenu,
            'prefix'       => $prefix,
            'productions'  => array(),
            'meta'         => $this->get('hopitalnumerique_recherche.manager.search')->getMetas($contenu->getReferences(), $contenu->getContenu() ),
            'ambassadeurs' => $this->getAmbassadeursConcernes( $objet->getId() )
        ));
    }

    /**
     * Article Action
     */
    public function articleAction($categorie, $id, $alias)
    {
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $id ) );

        //Si l'user connecté à le rôle requis pour voir l'objet
        if( $this->checkAuthorization( $objet ) === false )
            return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );

        //on récupère l'item de menu courant
        $request     = $this->get('request');
        $routeName   = $request->get('_route');
        $routeParams = json_encode($request->get('_route_params'));
        $item        = $this->get('nodevo_menu.manager.item')->findOneBy( array('route'=>$routeName, 'routeParameters'=>$routeParams) );

        //on récupère les actus
        $categories = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'parent' => 188) );

        //get Type
        $types = $this->get('hopitalnumerique_objet.manager.objet')->formatteTypes( $objet->getTypes() );

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Publication:articles.html.twig', array(
            'objet'      => $objet,
            'meta'       => $this->get('hopitalnumerique_recherche.manager.search')->getMetas($objet->getReferences(), $objet->getResume() ),
            'menu'       => $item ? $item->getMenu()->getAlias() : null,
            'categories' => $categories,
            'types'      => $types
        ));
    }

    /**
     * Affiche la synthèse de l'objet dans une grande popin
     */
    public function syntheseAction($id)
    {
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );

        //test si l'user connecté à le rôle requis pour voir la synthèse
        $user   = $this->get('security.context')->getToken()->getUser();
        $role   = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $params = array();
        if( $this->get('hopitalnumerique_objet.manager.objet')->checkAccessToObjet($role, $objet) )
            $params['objet'] = $objet;
        
        return $this->render('HopitalNumeriquePublicationBundle:Publication:synthese.html.twig', $params);
    }






    /**
     * Retorune le type de la prod
     *
     * @param  [type] $objet [description]
     *
     * @return [type]
     */
    private function getType( $objet ) 
    {
        $type  = array();
        $types = $objet->getTypes();

        foreach ($types as $one) {
            $parent = $one->getParent();
            if( !is_null($parent) && $parent->getId() == 175 )
                $type[] = $one->getLibelle();
        }
        //reformatte proprement les types
        $type = implode(' ♦ ', $type);

        return $type;
    }

    /**
     * Build productions with authorizations
     *
     * @param  [type] $prodLiees [description]
     *
     * @return [type]
     */
    private function getProductionsAssocies( $prodLiees )
    {
        $productions = array();
        foreach( $prodLiees as $prod){
            $tab = explode(':', $prod);

            //switch Objet / Infra-doc
            if( $tab[0] == 'PUBLICATION' ){
                $objet   = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $tab[1] ) );
                $contenu = false;
            }else if( $tab[0] == 'INFRADOC' ){
                $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array('id' => $tab[1] ) );
                $objet   = $contenu->getObjet();
            }
            
            if( $this->checkAuthorization( $objet ) === true ){
                $production        = new \StdClass;
                $production->id    = $objet->getId();
                $production->alias = $objet->getAlias();

                //Cas Objet
                if( $contenu === false ) {
                    //formate datas
                    $production->titre    = $objet->getTitre();
                    $production->created  = $objet->getDateCreation();
                    $production->objet    = true;
                    $resume               = explode('<!-- pagebreak -->', $objet->getResume() );
                    $production->synthese = $objet->getSynthese();
                }else{
                    //formate datas
                    $production->idc      = $contenu->getId();
                    $production->aliasc   = $contenu->getAlias();
                    $production->titre    = $contenu->getTitre();
                    $production->created  = $contenu->getDateCreation();
                    $production->objet    = false;
                    $production->synthese = null;
                    $resume               = explode('<!-- pagebreak -->', $contenu->getContenu() );
                }

                $production->resume   = html_entity_decode(strip_tags($resume[0]), 2 | 0, 'UTF-8');
                $production->updated  = false;
                $production->new      = false;
                $production->type     = $this->getType($objet);

                $productions[] = $production;
            }
        }

        //update status updated + new
        $user        = $this->get('security.context')->getToken()->getUser();
        $productions = $this->get('hopitalnumerique_objet.manager.consultation')->updateProductionsWithConnectedUser( $productions, $user );

        return $productions;
    }

    /**
     * Récupère les objets de la recherche et filtre sur 10 résulats maxi par catégorie
     *
     * @param Objet|Contenu $publication La publication (Objet ou Contenu)
     *
     * @return array
     */
    private function getObjetsFromRecherche( $publication )
    {
        //get Recherche results
        $refs       = array();
        $refs       = $this->getMoreRefs( $refs, $publication->getReferences() );
        
        //On récupère le role de l'user connecté
        $user = $this->get('security.context')->getToken()->getUser();
        $role = $this->get('nodevo_role.manager.role')->getUserRole($user);

        //on récupère sa recherche
        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
        $objets = $this->get('hopitalnumerique_recherche.manager.search')->getObjetsForRecherche( $refs, $role, $refsPonderees );
        $objets = $this->get('hopitalnumerique_objet.manager.consultation')->updateObjetsWithConnectedUser( $objets, $user );
    
        //make array unique
        $ids = array();
        foreach($objets as $objet) {
            if( $publication->getId() != $objet['id'] )
                $ids[] = $objet['id'];
        }
        $ids = array_unique($ids);
        $newObjets = array();
        foreach($objets as $objet) {
            if( in_array($objet['id'], $ids) )
                $newObjets[] = $objet;
        }

        return $this->get('hopitalnumerique_recherche.manager.search')->formatForPublication( $newObjets, $publication );
    }

    /**
     * Retourne la liste des ambassadeurs concernés par la production
     *
     * @param Objet $objet La production consultée
     *
     * @return array
     */
    private function getAmbassadeursConcernes( $objet )
    {
        //get connected user and his region
        $user   = $this->get('security.context')->getToken()->getUser();
        $region = $user === 'anon.' ? false : $user->getRegion();

        return $this->get('hopitalnumerique_user.manager.user')->getAmbassadeursByRegionAndProduction( $region, $objet );
    }

    /**
     * Vérifie que l'objet est accessible à l'user connecté ET que l'objet est toujours bien publié
     *
     * @param Objet $objet L'objet
     *
     * @return boolean
     */
    private function checkAuthorization( $objet )
    {
        $user    = $this->get('security.context')->getToken()->getUser();
        $role    = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $message = 'Vous n\'avez pas accès à cette publication.';

        //test si l'user connecté à le rôle requis pour voir l'objet
        if( !$this->get('hopitalnumerique_objet.manager.objet')->checkAccessToObjet($role, $objet) ) {
            return false;
        }

        $today = new \DateTime();

        //test si l'objet est publié
        if( !is_null($objet->getDateDebutPublication()) && $today < $objet->getDateDebutPublication() ){
            $this->get('session')->getFlashBag()->add('warning', $message );
            return false;
        }

        //test si l'objet est toujours publié
        if( !is_null($objet->getDateFinPublication()) && $today > $objet->getDateFinPublication() ){
            $this->get('session')->getFlashBag()->add('warning', $message );
            return false;
        }

        //test si l'objet est actif : état actif === 3
        if( $objet->getEtat()->getId() != 3 ){
            $this->get('session')->getFlashBag()->add('warning', $message );
            return false;
        }

        return true;
    }

    /**
     * Récupère les références de la production consulté
     *
     * @param array $refs       Les refs
     * @param array $references Les RefObjet/RefContenu
     *
     * @return array
     */
    private function getMoreRefs($refs, $references )
    {
        //prépare les categs
        $categs = array( 220 => 'categ1', 221 => 'categ2', 223 => 'categ3', 222 => 'categ4' );
        //get refs from consulted object
        foreach( $references as $reference ) {
            $one = $reference->getReference();

            if( !is_null($one->getParent()) ) {
                $parentId = $one->getParent()->getId();
                //get Grand Parent if needed
                if ( !in_array($parentId, array_keys($categs)) ){
                    $parentId = $one->getParent()->getParent()->getId();

                    //get Arrière Grand Parent if needed
                    if ( !in_array($parentId, array_keys($categs)) )
                        $parentId = $one->getParent()->getParent()->getParent()->getId();
                }

                if( !isset($refs[ $categs[$parentId] ]) )
                    $refs[ $categs[$parentId] ] = array();
                
                $refs[ $categs[$parentId] ][] = $one->getId();
            }
        }

        return $refs;
    }
}
