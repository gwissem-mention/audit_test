<?php
namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ConsultationController extends Controller
{
    /**
     * Delete d'une consultation (AJAX)
     *
     * @param integer $id ID de la consultation à supprimer
     */
    public function deleteAction($id)
    {
        $consultation = $this->get('hopitalnumerique_objet.manager.consultation')->findOneBy( array( 'id' => $id ) );
        
        //Suppression de l'entitée
        $this->get('hopitalnumerique_objet.manager.consultation')->delete( $consultation );
        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopital_numerique_requete_homepage').'"}', 200);
    }
}