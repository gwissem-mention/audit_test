<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RequeteController extends Controller
{
    /**
     * Affichage de la liste des requêtes de l'utilisateur connecté
     */
    public function indexAction()
    {
        //get connected user
        $user = $this->get('security.context')->getToken()->getUser();

        //get requetes
        $requetes = $this->get('hopitalnumerique_recherche.manager.requete')->findBy( array( 'user' => $user ) );

        return $this->render('HopitalNumeriqueRechercheBundle:Requete:index.html.twig', array(
            'requetes' => $requetes
        ));
    }

    /**
     * Save en AJAX de la requête
     */
    public function saveAction()
    {
        $nom        = $this->get('request')->request->get('nom');
        $references = $this->get('request')->request->get('references');

        //on crée une nouvelle requete
        $requete = $this->get('hopitalnumerique_recherche.manager.requete')->createEmpty();

        //get connected user
        $user = $this->get('security.context')->getToken()->getUser();

        $requete->setNom( $nom );
        $requete->setRefs( $references );
        $requete->setUser( $user );

        //s'il n'existe pas encore de requête pour cet utilisateur, on met celle la en requête par défaut
        $tmp = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array( 'user' => $user ) );
        if( !$tmp )
            $requete->setIsDefault( true );

        $this->get('hopitalnumerique_recherche.manager.requete')->save( $requete );

        return new Response('{"success":true}', 200);
    }

    /**
     * Delete d'une requete (AJAX)
     *
     * @param integer $id ID de la requete à supprimer
     */
    public function deleteAction($id)
    {
        $requete = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array( 'id' => $id ) );

        //Suppression de l'entitée
        $this->get('hopitalnumerique_recherche.manager.requete')->delete( $requete );

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
            $requete->setIsDefault( $isDefault );
        }
        $this->get('hopitalnumerique_recherche.manager.requete')->save( $requetes );

        $this->get('session')->getFlashBag()->add('info', 'Requête par défaut modifiée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopital_numerique_requete_homepage').'"}', 200);
    }
}