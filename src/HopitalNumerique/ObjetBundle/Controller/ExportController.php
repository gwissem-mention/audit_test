<?php

namespace HopitalNumerique\ObjetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use Symfony\Component\EventDispatcher\EventDispatcher;
use HopitalNumerique\ObjetBundle\Event\ObjetEvent;
use HopitalNumerique\ObjetBundle\Events;

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
    public function exportCsvAction($primaryKeys, $allPrimaryKeys)
    {
        //get all selected Users
        if ($allPrimaryKeys == 1) {
            $rawDatas = $this->get('hopitalnumerique_objet.grid.objet')->getRawData();
            foreach ($rawDatas as $data) {
                $primaryKeys[] = $data['id'];
            }
        }

        $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
        $objets        = $this->get('hopitalnumerique_objet.manager.objet')->getDatasForExport($primaryKeys, $refsPonderees);

        $colonnes = array(
                            'id'                   => 'ID publication',
                            'titre'                => 'Titre publication',
                            'alias'                => 'Alias publication',
                            'domaines'             => 'Domaine(s) associé(s)',
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
        					'sourceExterne'		   => 'Source Externe',
                            'types'                => 'Catégories de la publication',
                            'ambassadeurs'         => 'Ambassadeurs concernés par la publication',
                            'fichier1'             => 'Fichier 1',
                            'fichier2'             => 'Fichier 2',
                            'vignette'             => 'Vignette',
                            'referentAnap'         => 'Référent ANAP',
                            'sourceDocument'       => 'Source du document',
                            'commentairesFichier'  => 'Commentaires backoffice',
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
                            'objets'               => 'Productions liées',
                            'commentairesAssocies' => 'Commentaires frontoffice',
                            'cibleDiffusion'       => 'Cible de diffusion'
                        );

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_objet.manager.objet')->exportCsv($colonnes, $objets, 'export-publications.csv', $kernelCharset);
    }

    /**
     * Exporte un objet
     * @ParamConverter("objet", class="HopitalNumeriqueObjetBundle:Objet")
     * @return Fichier
     */
    public function exportAction(Objet $objet, $type)
    {
        $options = array(
            'serve_filename' => $objet->getAlias().'.'.$objet->getTypeMime($type),
            'absolute_path'  => false,
            'inline'         => false,
        );

        $fileName = $objet->getAbsolutePath($type);

        if (file_exists($fileName)) {
            $user = $this->get('security.context')->getToken()->getUser();

            $dispatcher = $this->get('event_dispatcher');
            //Dispatch sur l'événement objet_download_success
            $dispatcher->dispatch(Events::OBJET_DOWNLOAD_SUCCESS, new ObjetEvent($objet, ("anon." !== $user) ? $user : null), $type);

            return $this->get('igorw_file_serve.response_factory')->create($fileName, 'application/'.$objet->getTypeMime($type), $options);
        } else {
            // On envoi une 'flash' pour indiquer à l'utilisateur que le fichier n'existe pas: suppression manuelle sur le serveur
            $this->get('session')->getFlashBag()->add(('danger'), 'Le document n\'existe plus sur le serveur.');

            return $this->redirect($this->generateUrl('hopital_numerique_publication_publication_objet', array('id' => $objet->getId(), 'alias' => $objet->getAlias())));
        }
    }
}
