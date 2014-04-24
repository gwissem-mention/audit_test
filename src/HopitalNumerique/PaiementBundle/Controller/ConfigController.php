<?php

namespace HopitalNumerique\PaiementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Config controller.
 */
class ConfigController extends Controller
{
    /**
     * Page de configuration des prix
     */
    public function indexAction()
    {
        $remboursements = $this->get('hopitalnumerique_paiement.manager.remboursement')->findAll();





        return $this->render('HopitalNumeriquePaiementBundle:Config:index.html.twig', array(
            'remboursements' => $remboursements
        ));
    }

    /**
     * Sauvegarde le tableau de config
     *
     * @param Request $request La requête
     *
     * @return Redirect
     */
    public function saveAction(Request $request)
    {
        //get posted Datas
        $remboursements = $request->request->get('remboursement');

        //prepare 1 save only
        $toSave = array();

        //run on each lines
        foreach($remboursements as $id => $remboursement) {
            $entity = $this->get('hopitalnumerique_paiement.manager.remboursement')->findOneBy( array( 'id' => $id ) );
            $entity->setIntervention( intval($remboursement['intervention']) );
            $entity->setSupplement( intval($remboursement['supplement']) );
            $entity->setRepas( intval($remboursement['repas']) );
            $entity->setGestion( intval($remboursement['gestion']) );

            $toSave[] = $entity;
        }
        $this->get('hopitalnumerique_paiement.manager.remboursement')->save($toSave);

        // On envoi une 'flash' pour indiquer à l'utilisateur la mise à jour
        $this->get('session')->getFlashBag()->add( 'info' , 'Règles de calcul des remboursements mises à jour.' ); 

        return $this->redirect( $this->generateUrl('hopitalnumerique_paiement_config') );
    }
}