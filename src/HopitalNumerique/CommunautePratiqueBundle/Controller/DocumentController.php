<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Controller;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Document;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nodevo\ToolsBundle\Tools\Fichier;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
        if (null === $this->getUser())
        {
            return $this->redirect($this->generateUrl('hopital_numerique_homepage'));
        }

        return $this->render('HopitalNumeriqueCommunautePratiqueBundle:Document:listByGroupe.html.twig', array(
            'groupe' => $groupe,
            'documents' => $this->container->get('hopitalnumerique_communautepratique.manager.document')->findBy(array('groupe' => $groupe, 'user' => $this->getUser()))
        ));
    }

    /**
     * Lorsqu'un document est enregistré via jQuery File Upload.
     */
    public function uploadAction(Groupe $groupe, Request $request)
    {
        $response = array('files' => array());

        if (null !== $this->getUser())
        {
            $fileRequest = $request->files->all();

            foreach ($fileRequest['files'] as $fichierCharge)
            {
                $response['files'][] = $this->saveUploadedDocument($fichierCharge, $groupe);
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

        if ($documentFichier->move($this->container->get('kernel')->getRootDir().DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'communaute-de-pratiques'.DIRECTORY_SEPARATOR.'documents'.DIRECTORY_SEPARATOR.$uploadedFile->getClientOriginalName(), false))
        {
            $documentFichier->setNomMinifie($documentFichier->getFilenameWithoutExtension(), '-', false, 255, false);
            $nouveauDocument = $this->container->get('hopitalnumerique_communautepratique.manager.document')->createEmpty();
            $nouveauDocument->setNom($documentFichier->getFilename());
            $nouveauDocument->setLibelle($uploadedFile->getClientOriginalName());
            $nouveauDocument->setSize($documentFichier->getSize());
            $nouveauDocument->setGroupe($groupe);
            $nouveauDocument->setUser($this->getUser());
            $this->container->get('hopitalnumerique_communautepratique.manager.document')->save($nouveauDocument);
            
            $this->container->get('session')->getFlashBag()->add('success', 'Document "'.$uploadedFile->getClientOriginalName().'" enregistré.');
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

    /**
     * Supprime un document.
     */
    public function deleteAction(Document $document)
    {
        if ($this->container->get('hopitalnumerique_communautepratique.dependency_injection.security')->canDeleteDocument($document))
        {
            $this->container->get('hopitalnumerique_communautepratique.manager.document')->delete($document);
            return new JsonResponse(array('success' => true));
        }
        
        return new JsonResponse(array('success' => false));
    }
}
