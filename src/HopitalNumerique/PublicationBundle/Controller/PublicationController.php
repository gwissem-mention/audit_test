<?php

namespace HopitalNumerique\PublicationBundle\Controller;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PublicationController extends Controller
{
    /**
     * Objet Action
     */
    public function objetAction(Request $request, Objet $objet)
    {
        $domaineId = $request->getSession()->get('domaineId');

        if (!in_array($domaineId, $objet->getDomainesId())) {
            throw $this->createNotFoundException("La publication n'appartient pas au domaine courant.");
        }

        $domaine = $this->get('hopitalnumerique_domaine.manager.domaine')->findOneById($domaineId);

        //objet visualisation
        if(!$this->get('security.context')->isGranted('ROLE_ADMINISTRATEUR_1'))
        {
            $objet->setNbVue( ($objet->getNbVue() + 1) );
            $this->get('hopitalnumerique_objet.manager.objet')->save($objet);
        }

        //Si l'user connecté à le rôle requis pour voir l'objet
        if( $this->checkAuthorization( $objet ) === false ){
            $urlPublication = $this->generateUrl('hopital_numerique_publication_publication_objet', array('id' => $objet->getId()));
            $urlPublication = rtrim(strtr(base64_encode($urlPublication), '+/', '-_'), '=');
            return $this->redirect( $this->generateUrl('account_login', array('urlToRedirect' => $urlPublication) ) );
        }
        
        //Types objet
        $types = $this->get('hopitalnumerique_objet.manager.objet')->formatteTypes( $objet->getTypes() );

        //set Consultation entry
        if(!$objet->isArticle())
        {
            $this->get('hopitalnumerique_objet.manager.consultation')->consulted( $domaine, $objet );
        }

        $objetsOrder = array();

        $objets = $this->getObjetsFromRecherche( $objet );

        //build productions with authorizations
        $productions = $this->getProductionsAssocies($objet->getObjets());

        //get Contenus : for sommaire
        $contenus = $objet->isInfraDoc() ? $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjet( $objet->getId() ) : array();

        foreach ($objets as $key => $objetTemp) 
        {
            if(array_key_exists('objet', $objetTemp) && !is_null($objetTemp["objet"]))
            {
                $objetsOrder['-' . $objetTemp["id"]] = $objetTemp;
            }
            else
            {
                $objetsOrder[$objetTemp["id"]] = $objetTemp;
            }
        }

        foreach ($objetsOrder as $key => $objetCurrent) 
        {
            if (array_key_exists('objet', $objetCurrent) && !is_null($objetCurrent['objet'])) 
            {
                $libContenu = $this->get('hopitalnumerique_objet.manager.contenu')->getPrefix($this->get('hopitalnumerique_objet.manager.contenu')->findOneBy(array('id' => $objetCurrent['id'])));
                $objetsOrder[$key]['prefixe'] = $libContenu;
                $objetsOrder[$key]['parent']  = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(array('id' => $objetCurrent['objet']));
            }
        }

        //Ajout des objets liés au prods
        foreach ($productions as $production) 
        {
            $libContenu = "";

            if(!is_null($production->idc))
            {
                $libContenu = $this->get('hopitalnumerique_objet.manager.contenu')->getPrefix($this->get('hopitalnumerique_objet.manager.contenu')->findOneBy(array('id' => $production->idc)));
            }

            $objetsOrder[$production->id]['prefixe'] = $libContenu;
            $objetsOrder[$production->id]['parent']  = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(array('id' => $production->id));
        }

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Publication:objet.html.twig', array(
            'objet'        => $objet,
            'objets'       => $objets,
            'objetsOrder'  => $objetsOrder,
            'note'         => $this->container->get('hopitalnumerique_objet.doctrine.note_reader')->getNoteByObjetAndUser($objet, $this->getUser()),
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
    public function contenuAction(Request $request, $id, $alias = null, $idc, $aliasc = null)
    {
        $domaineId = $request->getSession()->get('domaineId');
        $domaine   = $this->get('hopitalnumerique_domaine.manager.domaine')->findOneById($domaineId);

        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $id ) );

        if (!in_array($domaineId, $objet->getDomainesId())) {
            throw $this->createNotFoundException("La publication n'appartient pas au domaine courant.");
        }

        //Si l'user connecté à le rôle requis pour voir l'objet
        if( $this->checkAuthorization( $objet ) === false )
        {
            if(is_null($alias))
            {
                $urlPublication = $this->generateUrl('hopital_numerique_publication_publication_contenu_without_alias', array('id' => $id, 'idc' => $idc));
            }
            else
            {
                $urlPublication = $this->generateUrl('hopital_numerique_publication_publication_contenu', array('id' => $id, 'idc' => $idc, 'alias' => $alias, 'aliasc' => $aliasc));
            }
            $urlPublication = rtrim(strtr(base64_encode($urlPublication), '+/', '-_'), '=');
            return $this->redirect( $this->generateUrl('account_login', array('urlToRedirect' => $urlPublication) ) );
            // return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );
        }

        //on récupère le contenu
        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array( 'id' => $idc ) );
        $contenuObjets = $this->getProductionsAssocies($contenu->getObjets());

        $prefix  = $this->get('hopitalnumerique_objet.manager.contenu')->getPrefix($contenu);

        //objet visualisation
        if(!$this->get('security.context')->isGranted('ROLE_ADMINISTRATEUR_1'))
        {
            $contenu->setNbVue( ($contenu->getNbVue() + 1) );
            $this->get('hopitalnumerique_objet.manager.contenu')->save($contenu);
        }

        //set Consultation entry
        if(!$objet->isArticle())
        {
            $this->get('hopitalnumerique_objet.manager.consultation')->consulted( $domaine, $contenu, true );
        }

        $contenuTemp      = $contenu;
        $breadCrumbsArray = array();

        $objetsOrder = array();

        $contenusNonVidesTries  = $this->get('hopitalnumerique_objet.manager.contenu')->getContenusNonVidesTries( $objet );

        $precedent      = $this->get('hopitalnumerique_objet.manager.contenu')->getPrecedent( $contenusNonVidesTries, $contenu );
        $precedentOrder = $this->get('hopitalnumerique_objet.manager.contenu')->getFullOrder($precedent);
        $suivant        = $this->get('hopitalnumerique_objet.manager.contenu')->getSuivant( $contenusNonVidesTries, $contenu );
        $suivantOrder   = $this->get('hopitalnumerique_objet.manager.contenu')->getFullOrder($suivant);

        //Types objet
        $types = $this->get('hopitalnumerique_objet.manager.objet')->formatteTypes( $objet->getTypes() );

        //get Contenus : for sommaire
        $contenus = $objet->isInfraDoc() ? $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjet( $id ) : array();

        $objets = $this->getObjetsFromRecherche( $contenu );

        //Ajout du contenu courant
        $breadCrumbsArray[] = array(
            'label'   => $this->get('hopitalnumerique_objet.manager.contenu')->getPrefix($contenu) . ' ' . $contenu->getTitre(),
            'contenu' => $contenu
        );
        $breadCrumbs      = "";

        while(!is_null($contenuTemp->getParent()))
        {
            $contenuTemp      = $contenuTemp->getParent();
            array_unshift($breadCrumbsArray, array(
                    'label'   => $this->get('hopitalnumerique_objet.manager.contenu')->getPrefix($contenuTemp) . ' ' . $contenuTemp->getTitre(),
                    'contenu' => $contenuTemp
                )
            );
        }

        //ObjetsOrder
        foreach ($this->getObjetsFromRecherche( $contenu ) as $key => $objetTemp)
        {
            $objetsOrder[$objetTemp["id"]] = $objetTemp;
        }

        foreach ($objetsOrder as $key => $objetCurrent)
        {
            if (array_key_exists('objet', $objetCurrent) && !is_null($objetCurrent['objet']) )
            {
                $libContenu = $this->get('hopitalnumerique_objet.manager.contenu')->getPrefix($this->get('hopitalnumerique_objet.manager.contenu')->findOneBy(array('id' => $objetCurrent['id'])));
                $objetsOrder[$key]['prefixe'] = $libContenu;
                $objetsOrder[$key]['parent']  = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(array('id' => $objetCurrent['objet']));
            }
        }

        $meta = $this->get('hopitalnumerique_recherche.manager.search')->getMetas($contenu->getReferences(), $contenu->getContenu() );

        $ambassadeurs = $this->getAmbassadeursConcernes( $objet->getId() );

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Publication:objet.html.twig', array(
            'objet'            => $objet,
            'objets'           => $objets,
            'objetsOrder'      => $objetsOrder,
            'note'             => $this->container->get('hopitalnumerique_objet.doctrine.note_reader')->getNoteByContenuAndUser($contenu, $this->getUser()),
            'contenus'         => $contenus,
            'types'            => $types,
            'contenu'          => $contenu,
            'contenuObjets' => $contenuObjets,
            'breadCrumbsArray' => $breadCrumbsArray,
            'prefix'           => $prefix,
            'productions'      => array(),
            'meta'             => $meta,
            'ambassadeurs'     => $ambassadeurs,
            'precedent'        => $precedent,
            'precedentOrder'   => $precedentOrder,
            'suivant'          => $suivant,
            'suivantOrder'     => $suivantOrder
        ));
    }

    /**
     * Article Action
     */
    public function articleAction(Request $request, $categorie, $id, $alias)
    {
        $domaineId = $request->getSession()->get('domaineId');
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $id ) );

        if (!in_array($domaineId, $objet->getDomainesId())) {
            throw $this->createNotFoundException("La publication n'appartient pas au domaine courant.");
        }

        //Si l'user connecté à le rôle requis pour voir l'objet
        if( $this->checkAuthorization( $objet ) === false )
        {
            $urlPublication = $this->generateUrl('hopital_numerique_publication_publication_article', array('categorie' => $categorie, 'id' => $id, 'alias' => $alias));
            $urlPublication = rtrim(strtr(base64_encode($urlPublication), '+/', '-_'), '=');
            return $this->redirect( $this->generateUrl('account_login', array('urlToRedirect' => $urlPublication) ) );
            // return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );
        }

        //on récupère l'item de menu courant
        $routeName   = $request->get('_route');
        $routeParams = json_encode($request->get('_route_params'));
        $item        = $this->get('nodevo_menu.manager.item')->findOneBy( array('route'=>$routeName, 'routeParameters'=>$routeParams) );

        //on récupère les actus
        $categories = $this->get('hopitalnumerique_reference.manager.reference')->findByParent($this->get('hopitalnumerique_reference.manager.reference')->findOneById(188));

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
            $parent = $one->getFirstParent();
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
            }else if( $tab[0] == 'ARTICLE' ){
                $objet   = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $tab[1] ) );
                $contenu = false;
            }

            if( $this->checkAuthorization( $objet ) === true ){
                $production        = new \StdClass;
                $production->id    = $objet->getId();
                $production->alias = $objet->getAlias();
                $production->source = $objet->getSource();

                //Cas Objet
                if( $contenu === false )
                {
                    //formate datas
                    $production->titre    = $objet->getTitre();
                    $production->created  = $objet->getDateCreation();
                    $production->objet    = true;
                    $resume               = explode('<!-- pagebreak -->', $objet->getResume() );
                    $production->synthese = $objet->getSynthese();
                    $production->idc      = null;
                }
                else
                {
                    //formate datas
                    $production->idc      = $contenu->getId();
                    $production->aliasc   = $contenu->getAlias();
                    $production->titre    = $contenu->getTitre();
                    $production->created  = $contenu->getDateCreation();
                    $production->objet    = false;
                    $production->synthese = null;
                    $resume               = explode('<!-- pagebreak -->', $contenu->getContenu() );
                }

                $production->resume   = $resume[0];
                $production->updated  = false;
                $production->new      = false;
                $production->type     = $this->getType($objet);

                $productions[] = $production;
            }
        }

        $request       = $this->get('request');
        $domaineId     = $request->getSession()->get('domaineId');

        //update status updated + new
        $user        = $this->get('security.context')->getToken()->getUser();
        $productions = $this->get('hopitalnumerique_objet.manager.consultation')->updateProductionsWithConnectedUser( $domaineId, $productions, $user );

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

        $domaineId = $this->get('request')->getSession()->get('domaineId');

        //on récupère sa recherche
        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
        $objets = $this->get('hopitalnumerique_recherche.manager.search')->getObjetsForRecherche( $refs, $role, $refsPonderees );
        $objets = $this->get('hopitalnumerique_objet.manager.consultation')->updateObjetsWithConnectedUser( $domaineId, $objets, $user );

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
                $newObjets[$objet['id']] = $objet;
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

            if( !is_null($one->getFirstParent()) ) {
                $parentId = $one->getFirstParent()->getId();
                //get Grand Parent if needed
                if ( !in_array($parentId, array_keys($categs)) && !is_null($one->getFirstParent()->getFirstParent())){
                    $parentId = $one->getFirstParent()->getFirstParent()->getId();

                    //get Arrière Grand Parent if needed
                    if ( !in_array($parentId, array_keys($categs)) && !is_null($one->getFirstParent()->getFirstParent()->getFirstParent()))
                        $parentId = $one->getFirstParent()->getFirstParent()->getFirstParent()->getId();
                }

                if( !isset($refs[ $categs[$parentId] ]) )
                    $refs[ $categs[$parentId] ] = array();

                $refs[ $categs[$parentId] ][] = $one->getId();
            }
        }

        return $refs;
    }
}
