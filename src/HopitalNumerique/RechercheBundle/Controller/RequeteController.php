<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RequeteController extends Controller
{
    /**
     * Affichage de la liste des requêtes de l'utilisateur connecté
     */
    public function indexAction(Request $request, $indexVue)
    {
        //get connected user
        $user      = $this->get('security.context')->getToken()->getUser();
        $domaineId = $request->getSession()->get('domaineId');

        //get requetes
        $requetes      = $this->get('hopitalnumerique_recherche.manager.requete')->findBy( array( 'user' => $user, 'domaine' => $domaineId ) );
        $consultations = $this->get('hopitalnumerique_objet.manager.consultation')->getLastsConsultations( $user, $domaineId );

        if( $indexVue )
            return $this->render('HopitalNumeriqueRechercheBundle:Requete:index.html.twig', array('requetes' => $requetes, 'consultations' => $consultations));
        else
            return $this->render('HopitalNumeriqueRechercheBundle:Requete:mesrequetes.html.twig', array('requetes' => $requetes));
    }

    /**
     * Delete d'une requete (AJAX)
     *
     * @param integer $id ID de la requete à supprimer
     */
    public function deleteAction($id)
    {
        $requete = $this->get('hopitalnumerique_recherche.manager.requete')->findOneBy( array( 'id' => $id ) );

        //get connected user
        $user = $this->get('security.context')->getToken()->getUser();

        //Suppression de l'entitée
        $this->get('hopitalnumerique_recherche.manager.requete')->delete( $requete );

        //si on a supprimé la dernière requete par défaut, on met en défaut une autre requete 
        if($requete->isDefault()){
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
            $isDefault = ($requete->getId() == $id);
            $requete->setDefault( $isDefault );
        }
        $this->get('hopitalnumerique_recherche.manager.requete')->save( $requetes );

        $this->get('session')->getFlashBag()->add('info', 'Requête par défaut modifiée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopital_numerique_requete_homepage').'"}', 200);
    }
}