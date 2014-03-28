<?php

namespace HopitalNumerique\RegistreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nodevo\ToolsBundle\Tools\Chaine as NodevoChaine;

class AmbassadeurController extends Controller
{
    /**
     * Index Action
     */
    public function _indexAction( $region = null, $domaine = null )
    {
        //get connected user and Ambassadeurs
        $user = $this->get('security.context')->getToken()->getUser();
        
        //get User Role
        //Si il n'y pas d'utilisateur connecté, le tableau de role est vide
        $roles  = 'anon.' === $user ? array() : $user->getRoles();
        $isCMSI = in_array('ROLE_ARS_CMSI_4', $roles) ? true : false;

        //get region ( if not specified, get user's region)
        $region = is_null($region) ? $user->getRegion() : $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $region) );
        
        //get ambassadeurs liste
        $ambassadeurs = $region ? $this->get('hopitalnumerique_user.manager.user')->getAmbassadeursByRegionAndDomaine( $region, $domaine ) : array();
        
        //test if user is authorized to contact Ambassadeurs
        if( $isCMSI )
            $allowContact = ($user->getRegion() && $region && $region->getId() == $user->getRegion()->getId()) ? true : false;
        else
            $allowContact = true;

        //get liste des domaines fonctionnels
        $domaines = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'code' => 'PERIMETRE_FONCTIONNEL_DOMAINES_FONCTIONNELS') );

        return $this->render('HopitalNumeriqueRegistreBundle:Ambassadeur:index.html.twig', array(
            'ambassadeurs'    => $ambassadeurs,
            'domaines'        => $domaines,
            'domaineSelected' => $domaine,
            'allowContact'    => $allowContact,
            'region'          => $region ? $region : null
        ));
    }
    
    /**
     * Index Action
     */
    public function indexAction($domaine = null )
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
            $regionsJSON  = $session->get('registre-ambassadeur-region');
            $regions      = json_decode($regionsJSON);
        }
        //Sinon on charge la région de l'utilisateur
        else
        {
            
            //Si l'utilisateur courant n'a pas de région renseigné on le prévient qu'il n'y aura aucune région selectionné par défaut
            if(is_null($user->getRegion()))
            {
                $this->get('session')->getFlashBag()->add( 'info' , 'Vous n\'avez pas renseigné votre région.');
            }
            //sinon on récupère sa région courante
            else
            {
                //Récupère le nom de la région pour le minifier
                $libelleRegion = new NodevoChaine($user->getRegion()->getLibelle());

                $regionsJSON    = json_encode(array($libelleRegion));
                $regions = array($user->getRegion());
            }
        }
        
        //Pour l'ensemble des régions sélectionnées, récupération des ambassadeurs
        foreach ($regions as $region)
        {
            $ambassadeurs = array_merge($ambassadeurs, $this->get('hopitalnumerique_user.manager.user')->getAmbassadeursByRegionAndDomaine( $region, $domaine ));
        }
    
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