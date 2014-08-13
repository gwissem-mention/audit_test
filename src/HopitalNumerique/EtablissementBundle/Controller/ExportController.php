<?php
namespace HopitalNumerique\EtablissementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Export controller.
 */
class ExportController extends Controller
{
    /**
     * Export CSV de la liste des etablissement sélectionnés
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     */
    public function exportCsvAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_etablissement.grid.etablissement')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $refs = $this->get('hopitalnumerique_etablissement.manager.etablissement')->getDatasForExport( $primaryKeys );

        $colonnes = array( 
                            'id'                    => 'id', 
                            'nom'                   => 'Nom', 
                            'finess'                => 'FINESS Geographique', 
                            'typeOrganisme.libelle' => 'Type d\'établissement',
                            'region.libelle'        => 'Région',
                            'departement.libelle'   => 'Département',
                            'adresse'               => 'Adresse',
                            'ville'                 => 'Ville',
                            'codepostal'            => 'Code Postal'
                        );

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv( $colonnes, $refs, 'export-etablissements.csv', $kernelCharset );
    }

    /**
     * Export CSV de la liste des etablissement sélectionnés
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     */
    public function exportCsvAutresAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_user.grid.etablissement')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $refs = $this->get('hopitalnumerique_user.manager.user')->getEtablissementForExport( $primaryKeys );

        $colonnes = array(
                            'id'                              => 'id', 
                            'username'                        => 'Nom d\'utilisateur', 
                            'nom'                             => 'Nom', 
                            'prenom'                          => 'Prénom', 
                            'region'                          => 'Région',
                            'autreStructureRattachementSante' => 'Autre structure de rattachement santé',
                            'archiver'                        => 'Archivé ?'
                        );

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv( $colonnes, $refs, 'export-etablissements-autres.csv', $kernelCharset );
    }
}