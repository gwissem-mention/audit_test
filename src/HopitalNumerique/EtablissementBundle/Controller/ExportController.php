<?php

namespace HopitalNumerique\EtablissementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv(
            $colonnes,
            $refs,
            'export-etablissements.csv',
            $kernelCharset
        );
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
            'id'                              => 'id',
            'username'                        => 'Nom d\'utilisateur',
            'lastname'                        => 'Nom',
            'firstname'                       => 'Prénom',
            'region'                          => 'Région',
            'organizationLabel'               => 'Autre structure de rattachement santé',
            'archiver'                        => 'Archivé ?',
        ];

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv(
            $colonnes,
            $refs,
            'export-etablissements-autres.csv',
            $kernelCharset
        );
    }
}
