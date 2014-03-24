<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RequeteController extends Controller
{
    /**
     * Affichage de la liste des requêtes de l'utilisateur connecté
     */
    public function indexAction($indexVue)
    {
        //get connected user
        $user = $this->get('security.context')->getToken()->getUser();

        //get requetes
        $requetes = $this->get('hopitalnumerique_recherche.manager.requete')->findBy( array( 'user' => $user ) );

        if( $indexVue )
            return $this->render('HopitalNumeriqueRechercheBundle:Requete:index.html.twig', array('requetes' => $requetes));
        else
            return $this->render('HopitalNumeriqueRechercheBundle:Requete:mesrequetes.html.twig', array('requetes' => $requetes));
    }

    /**
     * Save en AJAX de la requête
     */
    public function saveAction()
    {
        $id         = $this->get('request')->request->get('id');
        $nom        = $this->get('request')->request->get('nom');
        $references = $this->get('request')->request->get('references');

        //get connected user
        $user = $this->get('security.context')->getToken()->getUser();

        $add = false;
        //cas AJOUT
        if( $id === ''){
            //on crée une nouvelle requete
            $requete = $this->get('hopitalnumerique_recherche.manager.requete')->createEmpty();
            $requete->setNom( $nom );
            $add = true;
        //cas UPDATE
        }else
            $requete = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array( 'user' => $user, 'id' => $id ) );

        $requete->setRefs( $references );
        $requete->setUser( $user );

        //s'il n'existe pas encore de requête pour cet utilisateur, on met celle la en requête par défaut
        $tmp = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array( 'user' => $user ) );
        if( !$tmp )
            $requete->setDefault( true );

        $this->get('hopitalnumerique_recherche.manager.requete')->save( $requete );

        $path = $this->generateUrl('hopital_numerique_recherche_homepage_requete', array('id'=>$requete->getId()));

        return new Response('{"success":true, "id":'.$requete->getId().', "nom":"'.$requete->getNom().'", "path":"'.$path.'","add":'.$add.', "def":'.( $requete->isDefault() ? 1 : 0 ).'}', 200);
    }

    /**
     * Delete d'une requete (AJAX)
     *
     * @param integer $id ID de la requete à supprimer
     */
    public function deleteAction($id)
    {
        $requete = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array( 'id' => $id ) );
        $default = $requete->isDefault();

        //get connected user
        $user = $this->get('security.context')->getToken()->getUser();

        //Suppression de l'entitée
        $this->get('hopitalnumerique_recherche.manager.requete')->delete( $requete );

        //si on a supprimé la dernière requete par défaut, on met en défaut une autre requete 
        if($default){
            $newRequete = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array( 'user' => $user) );
            if($newRequete){
                $newRequete->setDefault(true);
                $this->get('hopitalnumerique_recherche.manager.requete')->save( $newRequete );
            }
        }
        
        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopital_numerique_requete_homepage').'"}', 200);
    }

    /**
     * Edition du nom d'une requete (AJAX)
     *
     * @param integer $id ID de la requete à mettre à jour
     */
    public function editAction($id)
    {
        $requete = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array( 'id' => $id ) );
        $nom     = $this->get('request')->request->get('nom');
        $requete->setNom( $nom );

        //Suppression de l'entitée
        $this->get('hopitalnumerique_recherche.manager.requete')->save( $requete );

        $this->get('session')->getFlashBag()->add('info', 'Requête mise à jour avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopital_numerique_requete_homepage').'"}', 200);
    }

    /**
     * Toggle Default d'une requete (AJAX)
     *
     * @param integer $id ID de la requete à toggle
     */
    public function toggleAction($id)
    {
        //get connected user
        $user = $this->get('security.context')->getToken()->getUser();

        //get requetes
        $requetes = $this->get('hopitalnumerique_recherche.manager.requete')->findBy( array( 'user' => $user ) );
        foreach($requetes as $requete){
            $isDefault = $requete->getId() == $id ? true : false;
            $requete->setDefault( $isDefault );
        }
        $this->get('hopitalnumerique_recherche.manager.requete')->save( $requetes );

        $this->get('session')->getFlashBag()->add('info', 'Requête par défaut modifiée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopital_numerique_requete_homepage').'"}', 200);
    }
}