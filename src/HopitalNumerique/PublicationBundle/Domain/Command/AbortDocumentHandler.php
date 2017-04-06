<?php

namespace HopitalNumerique\PublicationBundle\Domain\Command;

use Doctrine\ORM\EntityNotFoundException;
use HopitalNumerique\PublicationBundle\Entity\Converter\Document;
use HopitalNumerique\PublicationBundle\Repository\Converter\DocumentRepository;
use HopitalNumerique\PublicationBundle\Service\Converter\MediaUploader;

class AbortDocumentHandler
{
    /** @var DocumentRepository $documentRepository */
    protected $documentRepository;
    /** @var MediaUploader $mediaUploader */
    protected $mediaUploader;

    /**
     * AbortDocumentHandler constructor.
     *
     * @param DocumentRepository $documentRepository
     * @param MediaUploader $mediaUploader
     */
    public function __construct(DocumentRepository $documentRepository, MediaUploader $mediaUploader)
    {
        $this->documentRepository = $documentRepository;
        $this->mediaUploader = $mediaUploader;
    }

    /**
     * @param AbortDocumentCommand $abortDocumentCommand
     */
    public function handle(AbortDocumentCommand $abortDocumentCommand)
    {
        /** @var Document $document */
        $document = $this->documentRepository->findOneByPublication($abortDocumentCommand->publicationId);

        if (is_null($document)) {
            throw new EntityNotFoundException();
        }

        foreach ($document->getTree()->getMedias(true) as $media) {
            $this->mediaUploader->removeMedia($media);
        }

        $this->documentRepository->remove($document);
    }
}
