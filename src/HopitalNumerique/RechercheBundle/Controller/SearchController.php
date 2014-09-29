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
        $categoriesProduction      = array();
        $categoriesProductionActif = "";
        $categoriesProduction    = $this->get('hopitalnumerique_reference.manager.reference')->findBy(array('parent' => '175'), array('libelle' => 'ASC'));

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
                $requete = null;
                $refs    = $session->get('requete-refs');
            //sinon on charge la requete par défaut
            }else{
                $requete = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array( 'user' => $user, 'isDefault' => true ) );
                $refs    = $requete ? json_encode($requete->getRefs()) : '[]';
                $categoriesProductionActif = $requete ? $requete->getCategPointDur() : '';

                //set requete id in session
                if( $requete )
                    $session->set('requete-id', $requete->getId());
            }
        //on charge la requete demandée explicitement
        }else{
            $requete = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array( 'user' => $user, 'id' => $id ) );

            if( $requete ) {
                $refs = json_encode($requete->getRefs());
                $categoriesProductionActif = is_null($requete->getCategPointDur()) ? '' : $requete->getCategPointDur();

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

        return $this->render('HopitalNumeriqueRechercheBundle:Search:index.html.twig', array(
            'elements'                      => $elements['CATEGORIES_RECHERCHE'],
            'requete'                       => $requete,
            'refs'                          => $refs,
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
                            $objetsOrder[$objet["id"]] = $objet;
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

            foreach ($objetsOrder as $key => $objetCurrent) 
            {
                if (!is_null($objetCurrent['objet'])) 
                {
                    $libContenu = $this->get('hopitalnumerique_objet.manager.contenu')->getPrefix($this->get('hopitalnumerique_objet.manager.contenu')->findOneBy(array('id' => $objetCurrent['id'])));
                    $objetsOrder[$key]['prefixe'] = $libContenu;
                    $objetsOrder[$key]['parent']  = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy(array('id' => $objetCurrent['objet']));
                }
            }
        }
        else
        {
            $categPointDurIdsArray = array();
        }

        //on prépare la session
        $session   = $this->getRequest()->getSession();
        $isRequete = (!is_null($session->get('requete-id')));
        $session->set('requete-refs', json_encode($references) );
        $session->set('requete-refs-categProd', $categPointDur );

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
            $this->get('hopitalnumerique_stat.manager.statrecherche')->sauvegardeRequete($references, $user, $categPointDur, count($objets), $isRequete);
        }

        return $this->render('HopitalNumeriqueRechercheBundle:Search:getResults.html.twig', array(
            'objets'              => $objets,
            'objetsOrder'         => $objetsOrder,
            'showMorePointsDurs'  => $showMorePointsDurs,
            'showMoreProductions' => $showMoreProductions
        ));
    }
}