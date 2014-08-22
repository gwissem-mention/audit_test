<?php

namespace HopitalNumerique\ModuleBundle\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Actions Export de mass controller.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class ExportMassController extends Controller
{
    /**
     * Export CSV de la liste des sessions 
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     */
    public function exportCsvAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected inscription
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_module.grid.inscription')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $inscriptions = $this->get('hopitalnumerique_module.manager.inscription')->findBy( array('id' => $primaryKeys) );

        $colonnes = array( 
                            'id'                        => 'id', 
                            'user.nom'                  => 'Nom', 
                            'user.prenom'               => 'Prénom', 
                            'user.username'             => 'Identifiant (login)', 
                            'user.email'                => 'Adresse e-mail',
                            'session.moduleTitre'       => 'Titre du module',
                            'session.dateSessionString' => 'Début de la session',
                            'etatInscription.libelle'   => 'Inscription',
                            'etatParticipation.libelle' => 'Participation',
                            'etatEvaluation.libelle'    => 'Evaluation',
                            'dateInscriptionString'     => 'Date d\'inscription'
                        );

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_module.manager.inscription')->exportCsv( $colonnes, $inscriptions, 'export-utilisateurs.csv', $kernelCharset );
    }
}