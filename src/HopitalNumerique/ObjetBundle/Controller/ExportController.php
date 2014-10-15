<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Export controller.
 */
class ExportController extends Controller
{
    /**
     * Export CSV de la liste des objets sélectionnés
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     */
    public function exportCsvAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_objet.grid.objet')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }

        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
        $objets        = $this->get('hopitalnumerique_objet.manager.objet')->getDatasForExport( $primaryKeys, $refsPonderees );

        $colonnes = array( 
                            'id'                   => 'ID publication', 
                            'titre'                => 'Titre publication',
                            'alias'                => 'Alias publication',
                            'note'                 => 'Note référencement',
                            'commentaires'         => 'Commentaires autorisé ?',
                            'synthese'             => 'Synthèse',
                            'resume'               => 'Résumé',
                            'notes'                => 'Notes autorisé ?',
                            'dateCreation'         => 'Date de création de la publication',
                            'dateParution'         => 'Date de parution de la publication',
                            'dateDebutPublication' => 'Date de début de la publication',
                            'dateFinPublication'   => 'Date de fin de la publication',
                            'dateModification'     => 'Date de modification de la publication',
                            'type'                 => 'Type de la publication',
                            'nbVue'                => 'Nombre de visualisation de la publication',
                            'noteMoyenne'          => 'Note moyenne de la publication',
                            'nombreNote'           => 'Nombre de note de la publication',
                            'etat'                 => 'Etat de la publication',
                            'roles'                => 'Accès interdit aux groupes',
                            'types'                => 'Catégories de la publication',
                            'ambassadeurs'         => 'Ambassadeurs concernés par la publication',
                            'fichier1'             => 'Fichier 1',
                            'fichier2'             => 'Fichier 2',
                            'vignette'             => 'Vignette',
                            'referentAnap'         => 'Référent ANAP',
                            'sourceDocument'       => 'Source du document',
                            'commentairesFichier'  => 'Commentaires',
                            'pathEdit'             => 'Fichier d\'administration',
                            'module'               => 'Module(s) de formation lié(s)',
                            'idParent'             => 'Id de la publication parente',
                            'idC'                  => 'ID infra-doc',
                            'titreC'               => 'Titre infra-doc',
                            'aliasC'               => 'Alias infra-doc',
                            'noteC'                => 'Note référencement de l\'infra-doc',
                            'orderC'               => 'Ordre de l\'infra-doc',
                            'dateCreationC'        => 'Date de création de l\'infra-doc',
                            'dateModificationC'    => 'Date de modification de l\'infra-doc',
                            'nbVueC'               => 'Nombre de visualisation de l\'infra-doc',
                            'noteMoyenneC'         => 'Note moyenne de l\'infra-doc',
                            'nombreNoteC'          => 'Nombre de notes de l\'infra-doc',
                            'objets'               => 'Productions liées'

                        );

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_objet.manager.objet')->exportCsv( $colonnes, $objets, 'export-publications.csv', $kernelCharset );
    }
}