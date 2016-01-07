<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Document;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nodevo\ToolsBundle\Tools\Fichier;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Nodevo\ToolsBundle\Tools\Systeme;

/**
 * Contrôleur concernant les documents.
 */
class DocumentController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * Liste des documents d'un groupe.
     */
    public function listByGroupeAction(Groupe $groupe)
    {
        if (!$this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')->canAccessCommunautePratique()) {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Document:listByGroupe.html.twig', array(
            'groupe' => $groupe,
            'documents' => $this->container->get('hopitalnumerique_communautepratique.manager.document')->findBy(array('groupe' => $groupe, 'user' => $this->getUser())),
            'iconesByExtension' => Document::$ICONES_BY_EXTENSION,
            'uploadMaxSize' => intval(Systeme::getFileUploadMaxSize() / 1024 / 1024)
        ));
    }

    /**
     * Lorsqu'un document est enregistré via jQuery File Upload.
     */
    public function uploadAction(Groupe $groupe, Request $request)
    {
        $response = array('files' => array());

        if (null !== $this->getUser()) {
            $fileRequest = $request->files->all();
            
            if (count($fileRequest) == 0) {
                $this->container->get('session')->getFlashBag()->add('danger', 'Aucun fichier n\'a été envoyé ou le poids total des fichiers est supérieur au maximum autorisé.');
            } else {
                $fichiersEnregistres = 0;
                $fichiersNonEnregistres = 0;

                foreach ($fileRequest['files'] as $fichierCharge) {
                    $uploadDocumentResponses = $this->saveUploadedDocument($fichierCharge, $groupe);
                    if ($uploadDocumentResponses[$fichierCharge->getClientOriginalName()]) {
                        $fichiersEnregistres++;
                    } else {
                        $fichiersNonEnregistres++;
                    }
                    $response['files'][] = $uploadDocumentResponses;
                }
                if ($fichiersEnregistres > 0) {
                    $this->container->get('session')->getFlashBag()->add('success', $fichiersEnregistres.' document'.($fichiersEnregistres > 1 ? 's ont été enregistrés' : ' a été enregistré').'.');
                }
            }
        }

        return new JsonResponse($response);
    }

    /**
     * Enregistre le document chargé par l'utilisateur.
     * 
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile      $uploadedFile Fichier chargé
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe       Groupe
     * @return array Informations sur le déroulement de la sauvegarde pour jQuery File Upload
     */
    private function saveUploadedDocument(UploadedFile $uploadedFile, Groupe $groupe)
    {
        $documentFichier = new Fichier($uploadedFile->getPathname());
        $extensionsAutorisees = explode(',', $this->container->getParameter('nodevo_gestionnaire_media.moxie_manager.extensions_autorisees'));

        if ($documentFichier->getSize() > Document::MAX_SIZE * 1024 * 1024) {
            $this->container->get('session')->getFlashBag()->add('danger', 'Document "'.$uploadedFile->getClientOriginalName().'" non enregistré car trop volumineux.');
        } elseif (!in_array(Fichier::getExtensionFromFile($uploadedFile->getClientOriginalName()), $extensionsAutorisees)) {
            $this->container->get('session')->getFlashBag()->add(
                'danger',
                'Document "'.$uploadedFile->getClientOriginalName().'" non enregistré car extension "'.
                Fichier::getExtensionFromFile($uploadedFile->getClientOriginalName()).'" non autorisée.'
            );
        } elseif ($uploadedFile->getSize() > 0 && '' != $uploadedFile->getFilename() && $uploadedFile->getError() == UPLOAD_ERR_OK) {
            if ($documentFichier->move($this->container->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'communaute-de-pratiques'.DIRECTORY_SEPARATOR.'documents'.DIRECTORY_SEPARATOR.$uploadedFile->getClientOriginalName(), false)) {
                $documentFichier->setNomMinifie($documentFichier->getFilenameWithoutExtension(), '-', false, 255, false);
                $nouveauDocument = $this->container->get('hopitalnumerique_communautepratique.manager.document')->createEmpty();
                $nouveauDocument->setNom($documentFichier->getFilename());
                $nouveauDocument->setLibelle($uploadedFile->getClientOriginalName());
                $nouveauDocument->setSize($documentFichier->getSize());
                $nouveauDocument->setGroupe($groupe);
                $nouveauDocument->setUser($this->getUser());
                $this->container->get('hopitalnumerique_communautepratique.manager.document')->save($nouveauDocument);

                return array
                (
                    $uploadedFile->getClientOriginalName() => true,
                    'name' => $uploadedFile->getClientOriginalName(),
                    'size' => $documentFichier->getSize()
                );
            }

            $this->container->get('session')->getFlashBag()->add('danger', 'Document "'.$uploadedFile->getClientOriginalName().'" non enregistré.');
            return array($uploadedFile->getClientOriginalName() => false); // Échec
        }
    }

    /**
     * Télécharge le document.
     */
    public function downloadAction(Document $document)
    {
        $options = array(
            'serve_filename' => $document->getNom(),
            'absolute_path'  => true,
            'inline'         => false
        );
    
        if (file_exists('../'.$document->getPathname()))
        {
            return $this->container->get('igorw_file_serve.response_factory')->create('../'.$document->getPathname(), 'application/force-download', $options);
        }
        
        return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
    }

    /**
     * Supprime un document.
     */
    public function deleteAction(Document $document)
    {
        $safeDelete = $this->container->get('hopitalnumerique_communautepratique.manager.commentaire')->safeDelete($document->getId());

        if($safeDelete == false) {
            return new JsonResponse(array('success' => false));
        } else {
            if ($this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')->canDeleteDocument($document))
            {
                $this->container->get('hopitalnumerique_communautepratique.manager.document')->delete($document);
                return new JsonResponse(array('success' => true));
            }
        }
    }
}
