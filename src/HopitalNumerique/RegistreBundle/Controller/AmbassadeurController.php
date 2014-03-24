<?php

namespace HopitalNumerique\RegistreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AmbassadeurController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction( $region = null, $domaine = null )
    {
        //get connected user and Ambassadeurs
        $user = $this->get('security.context')->getToken()->getUser();

        //get User Role
        $roles  = $user->getRoles();
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