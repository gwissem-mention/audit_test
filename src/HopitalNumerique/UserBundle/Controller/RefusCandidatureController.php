<?php

namespace HopitalNumerique\UserBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * RefusCandidature controller.
 */
class RefusCandidatureController extends Controller
{
    /**
     * Affiche la liste des RefusCandidature.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_user.grid.refuscandidature');

        return $grid->render('HopitalNumeriqueUserBundle:RefusCandidature:index.html.twig');
    }

    /**
     * Affiche le RefusCandidature en fonction de son ID passé en paramètre.
     * 
     * @param integer $id Id de RefusCandidature.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $refuscandidature = $this->get('hopitalnumerique_user.manager.refuscandidature')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueUserBundle:RefusCandidature:show.html.twig', array(
            'refuscandidature' => $refuscandidature,
        ));
    }

    /**
     * Export CSV de la liste des utilisateurs sélectionnés (caractérisation)
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     */
    public function exportCsvAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_user.grid.refuscandidature')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $refusCandidatures = $this->get('hopitalnumerique_user.manager.refuscandidature')->findBy( array('id' => $primaryKeys) );

        $colonnes = array( 
                            'id'              => 'id',
                            'user.nom'        => 'Nom', 
                            'user.prenom'     => 'Prénom', 
                            'user.username'   => 'Identifiant (login)', 
                            'user.email'      => 'Adresse e-mail',
                            'motifRefus'      => 'Motif du refus',
                            'dateRefusString' => 'Date du refus'
                        );

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.refuscandidature')->exportCsv( $colonnes, $refusCandidatures, 'export-refusCandidatures.csv', $kernelCharset );
    }
}