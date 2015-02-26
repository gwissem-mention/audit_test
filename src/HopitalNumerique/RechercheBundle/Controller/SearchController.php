<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction( $id = null )
    {
        $elements                  = $this->get('hopitalnumerique_reference.manager.reference')->getArboFormat(false, false, true);
        $categoriesProductionActif = "";
        $categoriesProduction    = $this->get('hopitalnumerique_reference.manager.reference')->findBy(array('parent' => '175'), array('order' => 'ASC'));

        //get connected user
        $user = $this->get('security.context')->getToken()->getUser();
        //on prépare la session
        $session = $this->getRequest()->getSession();


        //si on à charger une requete, on load la bonne url
        if ( is_null($id) && !is_null($session->get('requete-id')) )
            return $this->redirect( $this->generateUrl('hopital_numerique_recherche_homepage_requete', array('id'=>$session->get('requete-id'))) );

        //on essaye de charger la requete par défaut
        if ( is_null($id) ){
            //si on a quelque chose en session, on charge la session
            if( !is_null($session->get('requete-refs')) )
            {
                $categoriesProductionActif = ( !is_null( $session->get('requete-refs-categProd') ) ) ? $session->get('requete-refs-categProd') : '';
                $rechercheTextuelle        = ( !is_null( $session->get('requete-refs-recherche-textuelle') ) ) ? $session->get('requete-refs-recherche-textuelle') : '';
                $requete                   = null;
                $refs                      = $session->get('requete-refs');
            //sinon on charge la requete par défaut
            }else{
                $requete                   = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array( 'user' => $user, 'isDefault' => true ) );
                $refs                      = $requete ? json_encode($requete->getRefs()) : '[]';
                $categoriesProductionActif = $requete ? $requete->getCategPointDur() : '';
                $rechercheTextuelle        = $requete ? $requete->getRechercheTextuelle() : '';

                //set requete id in session
                if( $requete )
                    $session->set('requete-id', $requete->getId());
            }
        //on charge la requete demandée explicitement
        }else{
            $requete = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array( 'user' => $user, 'id' => $id ) );

            if( $requete ) {
                $refs                      = json_encode($requete->getRefs());
                $categoriesProductionActif = is_null($requete->getCategPointDur()) ? '' : $requete->getCategPointDur();
                $rechercheTextuelle        = is_null($requete->getRechercheTextuelle()) ? '' : $requete->getRechercheTextuelle();

                //update request
                $requete->setNew( false );
                $requete->setUpdated( false );
                $this->get('hopitalnumerique_recherche.manager.requete')->save($requete);

                //set requete id in session
                $session->set('requete-id', $id);
            }else{
                $session->set('requete-id', null);
                return $this->redirect( $this->generateUrl('hopital_numerique_recherche_homepage_requete') );
            }
        }

        if( $refs == 'null' )
            $refs = '[]';

        $session->set('requete-refs', $refs );
        $activationExalead = $this->get('hopitalnumerique_recherche.manager.search')->getActivationExalead();

        return $this->render('HopitalNumeriqueRechercheBundle:Search:index.html.twig', array(
            'elements'                      => $elements['CATEGORIES_RECHERCHE'],
            'requete'                       => $requete,
            'refs'                          => $refs,
            'rechercheTextuelle'            => $rechercheTextuelle,
            'activationExalead'             => $activationExalead,
            'categoriesProduction'          => $categoriesProduction,
            'categoriesProductionActif'     => $categoriesProductionActif,
            'categoriesProductionActifJSON' => json_encode(explode(',', $categoriesProductionActif))
        ));
    }

    /**
     * Retourne les résultats de la recherche
     */
    public function getResultsAction()
    {
        //On récupère le role de l'user connecté
        $user = $this->get('security.context')->getToken()->getUser();
        $role = $this->get('nodevo_role.manager.role')->getUserRole($user);

        $request       = $this->get('request');
        $references    = $request->request->get('references');

        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
        $objets        = $this->get('hopitalnumerique_recherche.manager.search')->getObjetsForRecherche( $references, $role, $refsPonderees );
        $objets        = $this->get('hopitalnumerique_objet.manager.consultation')->updateObjetsWithConnectedUser( $objets, $user );

        //GME 19/09/2014 : Ajout du filtre des categ point dur (liste à choix multiples)
        $categPointDur = $request->request->get('categPointDur');
        $objetsOrder   = array();

        //vvvvv GME 17/11/2014 : Ajout de la zone textuelle
        $rechercheTextuelle                 = $request->request->get('rechercheTextuelle');
        $resultatsTrouveeRechercheTextuelle = true;
        //^^^^^
        
        // YRO 20/01/2015 : cacher l'icone de pertinence
        $onlyText = false;
        
        // YRO 10/02/2015 : les occurences réellement trouvées dans les contenus
        $patternFounded = array();
        
        //vvvvv GME 21/11/2014 : Exalead
        if(trim($rechercheTextuelle) !== "")
        {
            $objetIds              = array();
            $contenuIds            = array();
            $allIds                = array();
            $optionsSearch         = $this->get('hopitalnumerique_recherche.manager.search')->getUrlRechercheTextuelle();
            //$urlRechercheTextuelle = "http://fifi.mind7.fr:13010/search-api/search?q=FACTEURS%20CLES%20DE%20SUCCES";
            $urlRechercheTextuelle = $optionsSearch . urlencode($rechercheTextuelle);
            
            $xml = simplexml_load_file($urlRechercheTextuelle);

            //Lien mort
            if($xml === FALSE)
            {
                $this->get('session')->getFlashBag()->add( 'danger', 'Un problème est survenu lors de votre recherche textuelle, merci de contacter un administrateur.' );
            }
            else
            {
                //Vérfication si des résultats sont remontés
                if(!is_null($xml->hits->Hit))
                {
                    foreach($xml->hits->Hit as $hit)
                    {
                        $hitUrl      = (string)$hit->attributes()->url;
                        $hitUrlArray = explode("=", $hitUrl);
                        
                        // YRO 10/02/2015 : les occurences réellement trouvées dans les contenus
                        foreach($hit->metas->Meta as $Meta){
                            if( $Meta->attributes()->name == "text" || $Meta->attributes()->name == "title" ){
                                foreach( $Meta->MetaText as $MetaText ){
                                    foreach( $MetaText->TextSeg as $key => $TextSeg ){
                                        if( $TextSeg->attributes()->highlighted == "true" ){
                                            $patternFounded[] = (string)$TextSeg;
                                        }
                                    }
                                }
                            }
                        }

                        if($hitUrlArray[0] == 'obj_id')
                        {
                            $objetIds[] = $allIds[] = intval(substr($hitUrlArray[1], 0 , -1));
                        }
                        elseif($hitUrlArray[0] == 'con_id')
                        {
                            $contenuIds[] = $allIds[] = intval(substr($hitUrlArray[1], 0 , -1));
                        }
                    }
                }
                else
                {
                    $resultatsTrouveeRechercheTextuelle = false;
                }
            }

            //Cas où l'on a juste de la recherche textuelle et pas de recherche par critère
            if(empty($objets))
            {
                $objetsRecherche   = $this->get('hopitalnumerique_objet.manager.objet')->findBy(array('id' => $objetIds));
                $contenusRecherche = $this->get('hopitalnumerique_objet.manager.contenu')->findBy(array('id' => $contenuIds));

                $objets = $this->get('hopitalnumerique_recherche.manager.search')->getObjetsForRechercheTextuelle( $objetsRecherche, $contenusRecherche, $role );
                
                // YRO 20/01/2015 : cacher l'icone de pertinence
                $onlyText = true;
            }
        }
        //^^^^^

        //Filtre uniquement si pas vide
        if(!empty($categPointDur))
        { 
            $categPointDurIdsArray = explode(',', $categPointDur);
            $categPointDurArray    = array();

            foreach ($categPointDurIdsArray as $categPointDurId) 
            {
                $categPointDurArray[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $categPointDurId))->getLibelle();
            }

            foreach ($objets as $key => $objet) 
            {
               if($objet["categ"] === "production")
               {
                    //Récupèration de tout les types de l'objet
                    $types = explode('♦', $objet["type"]);
                    $isInArray = false;
                    foreach ($types as $type) 
                    {
                        if(in_array(trim($type), $categPointDurArray))
                        {
                            $isInArray = true;
                            break;
                        }
                    }

                    if(!$isInArray)
                    {
                        //Supprime l'élément du tableau
                        unset($objets[$key]);
                    }
                }
            }
        }
        else
        {
            $categPointDurIdsArray = array();
        }
        
        if( true){//!$onlyText ){
            foreach ($objets as $key => $objet) 
            {
                if(array_key_exists('objet', $objet) && !is_null($objet["objet"]))
                {
                    $objetsOrder['-' . $objet["id"]] = $objet;
                }
                else
                {
                    $objetsOrder[$objet["id"]] = $objet;
                }
            }
        }

        $objetsRechercheTextuelle = array();

        //Dans le cas où une recherche textuelle est donnée
        if(trim($rechercheTextuelle) !== "")
        {
            //Dans le cas où il n'y a pas de filtre de critère de recherche
            // if(is_null($references))
            // {

            // }

            //Parcourt les objets 
            foreach ($objets as $objet) 
            {
                //Contenu
                if(array_key_exists('objet', $objet) && !is_null($objet["objet"]))
                {
                    if(in_array($objet['id'], $contenuIds))
                    {
                        $objetsRechercheTextuelle[] = $objet;
                    }
                }
                //Objet
                else
                {
                    if(in_array($objet['id'], $objetIds))
                    {
                        $objetsRechercheTextuelle[] = $objet;
                    }
                }
            }

            //$objets = $this->get('hopitalnumerique_recherche.manager.search')->getObjetsForRechercheTextuelle( $objetsRecherche, $contenusRecherche, $role );
            
            if( $onlyText ){
                $objetsRechercheTextuelleOrder = array();
                for($i = 0; $i < count($allIds); $i++)
                {
                    foreach($objetsRechercheTextuelle as $objet)
                    {
                        if($allIds[$i] == $objet['id'])
                        {
                            $objetsRechercheTextuelleOrder[] = $objet;
                            break;
                        }
                    }
                }
                $objetsRechercheTextuelle = $objetsRechercheTextuelleOrder;
            }
        }
        else
        {
            $objetsRechercheTextuelle = $objets;
        }

        foreach ($objetsOrder as $key => $objetCurrent) 
        {
            //Tout les cas ci-dessous sont en infrad
            if (array_key_exists('objet', $objetCurrent) && !is_null($objetCurrent['objet'])) 
            {
                //Dans le cas où une recherche textuelle est donnée
                if(trim($rechercheTextuelle) !== "")
                {
                    if(in_array($objetCurrent['id'], $contenuIds))
                    {
                        $libContenu = $this->get('hopitalnumerique_objet.manager.contenu')->getPrefix($this->get('hopitalnumerique_objet.manager.contenu')->findOneBy(array('id' => $objetCurrent['id'])));
                        $objetsOrder[$key]['prefixe'] = $libContenu;
                        $objetsOrder[$key]['parent']  = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(array('id' => $objetCurrent['objet']));
                    }
                }
                else
                {
                    $libContenu = $this->get('hopitalnumerique_objet.manager.contenu')->getPrefix($this->get('hopitalnumerique_objet.manager.contenu')->findOneBy(array('id' => $objetCurrent['id'])));
                    $objetsOrder[$key]['prefixe'] = $libContenu;
                    $objetsOrder[$key]['parent']  = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(array('id' => $objetCurrent['objet']));
                }
            }
        }

        //on prépare la session
        $session   = $this->getRequest()->getSession();
        $isRequete = (!is_null($session->get('requete-id')));
        $session->set('requete-refs', json_encode($references) );
        $session->set('requete-refs-categProd', $categPointDur );
        $session->set('requete-refs-recherche-textuelle', $rechercheTextuelle );

        //clean requete ID
        $cleanSession = $request->request->get('cleanSession');
        if( $cleanSession !== "false" )
            $session->set('requete-id', null);

        //get Cookies Stuff
        $cookies = $request->cookies;

        //set Cookies vals
        $showMorePointsDurs  = $cookies->has('showMorePointsDurs')  ? intval($cookies->get('showMorePointsDurs'))  : 2;
        $showMoreProductions = $cookies->has('showMoreProductions') ? intval($cookies->get('showMoreProductions')) : 2;

        //Sauvegarde des stats
        if(!is_null($references))
        {
            $categPointDur = is_null($categPointDur) ? '' : $categPointDur;
            $elements = $this->get('hopitalnumerique_reference.manager.reference')->getArboFormat(false, false, true);
            $this->get('hopitalnumerique_stat.manager.statrecherche')->sauvegardeRequete($references, $user, $categPointDur, count($objetsRechercheTextuelle), $isRequete);
        }

        return $this->render('HopitalNumeriqueRechercheBundle:Search:getResults.html.twig', array(
            'objets'              => $objetsRechercheTextuelle,
            'objetsOrder'         => $objetsOrder,
            'showMorePointsDurs'  => $showMorePointsDurs,
            'showMoreProductions' => $showMoreProductions,
            'onlyText'            => $onlyText, // YRO 20/01/2015 : cacher l'icone de pertinence
            'patternFounded'      => json_encode($patternFounded) // YRO 10/02/2015 : les occurences réellement trouvées dans les contenus
        ));
    }

    /**
     * Création de la vue "Type de production"
     *
     * @return [type]
     */
    public function getTypeProductionAction()
    {
        $request              = $this->get('request');
        $categPointDur        = $request->request->get('categPointDur');
        $categoriesProduction = array();

        if(is_array($categPointDur))
        {
            foreach ($categPointDur as $idCateg)
            {
                $categ = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $idCateg));
                $categoriesProduction[$categ->getId()] = $categ;
            }
        }

        return $this->render('HopitalNumeriqueRechercheBundle:Search:getTypeProduction.html.twig', array(
            'categoriesSelected' => $categoriesProduction,
        ));
    }
}