<?php

namespace HopitalNumerique\DocumentBundle\Controller;

use HopitalNumerique\DocumentBundle\Entity\Document;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ViewController extends Controller
{
    /**
     * @param Document $document
     *
     * @return BinaryFileResponse
     */
    public function viewAction(Document $document)
    {
        if (!$this->getUser() || $document->getUser()->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        $file = $document->getFile();
        if (null === $file) {
            throw $this->createNotFoundException();
        }

        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $response->getFile()->getFilename()
        );

        return $response;
    }
}
