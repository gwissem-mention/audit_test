<?php

namespace HopitalNumerique\EtablissementBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Export controller.
 */
class ExportController extends Controller
{
    /**
     * Export CSV de la liste des etablissement sélectionnés.
     *
     * @param array $primaryKeys
     * @param array $allPrimaryKeys
     *
     * @return Response
     */
    public function exportCsvAction($primaryKeys, $allPrimaryKeys)
    {
        ini_set('memory_limit','1024M');
        ini_set('max_execution_time', 0);

        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_etablissement.grid.etablissement')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }

        $refs = $this->get('hopitalnumerique_etablissement.manager.etablissement')->getDatasForExport($primaryKeys);

        $colonnes = [
                            'id' => 'id',
                            'nom' => 'Nom',
                            'finess' => 'FINESS Geographique',
                            'typeOrganisme.libelle' => 'Type de structure',
                            'region.libelle' => 'Région',
                            'departement.libelle' => 'Département',
                            'adresse' => 'Adresse',
                            'ville' => 'Ville',
                            'codepostal' => 'Code Postal',
                        ];

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this
            ->get('hopitalnumerique_user.manager.user')
            ->exportCsv($colonnes, $refs, 'export-etablissements.csv', $kernelCharset)
        ;
    }

    /**
     * Export CSV de la liste des etablissement sélectionnés.
     *
     * @param array $primaryKeys
     * @param array $allPrimaryKeys
     *
     * @return Response
     */
    public function exportCsvAutresAction($primaryKeys, $allPrimaryKeys)
    {
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_user.grid.etablissement')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }

        $refs = $this->get('hopitalnumerique_user.manager.user')->getEtablissementForExport($primaryKeys);

        $colonnes = [
            'id' => 'id',
            'username' => 'Nom d\'utilisateur',
            'lastname' => 'Nom',
            'firstname' => 'Prénom',
            'region' => 'Région',
            'organizationLabel' => 'Autre structure de rattachement santé',
            'domainName' => 'Domaine(s)',
            'archiver' => 'Archivé ?',
        ];

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this
            ->get('hopitalnumerique_user.manager.user')
            ->exportCsv($colonnes, $refs, 'export-etablissements-autres.csv', $kernelCharset)
        ;
    }
}
