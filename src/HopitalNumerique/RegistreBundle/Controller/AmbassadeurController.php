<?php

namespace HopitalNumerique\RegistreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nodevo\ToolsBundle\Tools\Chaine as NodevoChaine;

class AmbassadeurController extends Controller
{    
    /**
     * Index Action
     */
    public function indexAction( $domaine = null )
    {
        //Liste des régions sélectionnées
        $regions      = array();
        
        //Liste des ambassadeurs correspondant aux régions sélectionnées
        $ambassadeurs = array();
        
        //Recupère l'utilisateur connecté
        $user = $this->get('security.context')->getToken()->getUser();
    
        //get User Role
        //Si il n'y pas d'utilisateur connecté, le tableau de role est vide
        $roles  = 'anon.' === $user ? array() : $user->getRoles();
        $isCMSI = in_array('ROLE_ARS_CMSI_4', $roles) ? true : false;
        
        //On prépare la session
        $session = $this->getRequest()->getSession();
        
        //Si on a quelque chose en session, on charge la session
        if( !is_null($session->get('registre-ambassadeur-region')) )
        {
            //Récupération des régions en session
            $regionsJSON    = $session->get('registre-ambassadeur-region');
            //Decodage du JSOn pour avoir un tableau php
            $libellesRegion = json_decode($regionsJSON);
                        
            //Récupération de l'ensemble des régions car dans les sessions sont stockés les libellés, il nous faut les entités
            $allRegions = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array('code' => 'REGION'), array('libelle' => 'ASC'));
            
            foreach ($allRegions as $region)
            {
                //Récupère le nom de la région pour le minifier
                $libelleRegion = new NodevoChaine($region->getLibelle());
                
                //Si la région fait parti des régions passées en session
                if(in_array($libelleRegion->minifie(''), $libellesRegion))
                {
                    $regions[] = $region;
                }
                //Cas particulier de l'océan indien
                if('oceanindien' === $libelleRegion->minifie('') && (in_array('mayotte', $libellesRegion) || in_array('reunion', $libellesRegion)))
                {
                    $regions[] = $region;
                }
            }
        }
        //Sinon on charge la région de l'utilisateur
        else
        {
            
            //Si l'utilisateur courant n'a pas de région renseigné on le prévient qu'il n'y aura aucune région selectionné par défaut
            if(is_null($user->getRegion()))
            {
                $regionsJSON = json_encode(array());
            }
            //sinon on récupère sa région courante
            else
            {
                //Récupère le nom de la région pour le minifier
                $libelleRegion = new NodevoChaine($user->getRegion()->getLibelle());

                $regions        = array($user->getRegion());
                //Cas particulier de l'océan indien
                if("oceanindien" === $libelleRegion->minifie(''))
                {
                    $regionsJSON    = json_encode(array('mayotte','reunion'));
                }
                else 
                {
                    $regionsJSON    = json_encode(array($libelleRegion->minifie('')));
                }
            }
        }
        
        //Pour l'ensemble des régions sélectionnées, récupération des ambassadeurs
        foreach ($regions as $region)
        {
            if(!array_key_exists($region->getId(), $ambassadeurs))
            {
                $ambassadeurs[$region->getId()] = array();
            }
            
            $ambassadeurs[$region->getId()] = array_merge($ambassadeurs[$region->getId()], $this->get('hopitalnumerique_user.manager.user')->getAmbassadeursByRegionAndDomaine( $region, $domaine ));
        }
        
        $session->set('registre-ambassadeur-region', $regionsJSON );
    
        //get liste des domaines fonctionnels
        $domaines = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'code' => 'PERIMETRE_FONCTIONNEL_DOMAINES_FONCTIONNELS') );

        return $this->render('HopitalNumeriqueRegistreBundle:Ambassadeur:index.html.twig', array(
                'user'            => array(
                    'user'   => $user,
                    'isCMSI' => $isCMSI
                 ),
                'ambassadeurs'    => $ambassadeurs,
                'domaines'        => array(
                    'domaines'         => $domaines,
                    'domaineSelected'  => $domaine
                ),
                'regions'         => array(
                     'regions'          => $regions,
                     'regionsSelected'  => $regionsJSON
                )
        ));
    }
    
    /*
     * Met à jour la session de l'utilisateur avec les régions sélectionnées
     */
    public function editerSessionAction()
    {
        $domaine = intval($this->get('request')->request->get('domaine'));
        
        $regionJSON = $this->get('request')->request->get('regionJSON');
        
        //On prépare la session
        $session = $this->getRequest()->getSession();

        $session->set('registre-ambassadeur-region', $regionJSON );
        
        //Si il n'y a pas de domaine fonctionnel sélectionné, ne pas passer un param null permet d'éviter le "/0" à la fin de l'url
        if(0 != $domaine)
            return new Response('{"success":true, "url" : "'.$this->generateUrl( 'hopital_numerique_registre_homepage', array('domaine' => $domaine) ).'"}', 200);
        else
            return new Response('{"success":true, "url" : "'.$this->generateUrl( 'hopital_numerique_registre_homepage' ).'"}', 200);
    }

    /**
     * Affiche la liste des objets maitrisés par l'ambasssadeur dans une popin
     *
     * @param integer $id ID de l'user
     */
    public function objetsAction( $id )
    {
        //Récupération de l'utilisateur passé en param
        $objets = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsByAmbassadeur($id);
    
        return $this->render('HopitalNumeriqueRegistreBundle:Ambassadeur:objets.html.twig', array(
            'objets' => $objets
        ));
    }
    
    /**
     * Affiche la liste des domaines maitrisés par l'ambasssadeur dans une popin
     *
     * @param integer $id ID de l'user
     */
    public function domainesAction( $id )
    {        
        $ambassadeur = $this->get('hopitalnumerique_user.manager.user')->findOneBy(array('id' => $id));
        
        return $this->render('HopitalNumeriqueRegistreBundle:Ambassadeur:domaines.html.twig', array(
                'domaines' => $ambassadeur->getDomaines()
        ));
    }
}