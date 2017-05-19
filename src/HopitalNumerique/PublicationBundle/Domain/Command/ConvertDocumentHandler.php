<?php

namespace HopitalNumerique\PublicationBundle\Domain\Command;

use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ObjetBundle\Repository\ObjetRepository;
use HopitalNumerique\PublicationBundle\Entity\Converter\Document\Media;
use HopitalNumerique\PublicationBundle\Entity\Converter\Document\NodeInterface;
use HopitalNumerique\PublicationBundle\Repository\Converter\DocumentRepository;
use HopitalNumerique\PublicationBundle\Service\Converter\Extractor\ConverterInterface;
use HopitalNumerique\PublicationBundle\Service\Converter\MediaUploader;

class ConvertDocumentHandler
{
    /**
     * @var ConverterInterface
     */
    protected $converter;

    /**
     * @var DocumentRepository
     */
    protected $documentRepository;

    /**
     * @var ObjetRepository
     */
    protected $objetRepository;

    /**
     * @var MediaUploader
     */
    protected $mediaUploader;

    /**
     * ConvertDocumentHandler constructor.
     * @param ConverterInterface $converter
     * @param DocumentRepository $documentRepository
     */
    public function __construct(ConverterInterface $converter, DocumentRepository $documentRepository, ObjetRepository $objetRepository, MediaUploader $mediaUploader)
    {
        $this->converter = $converter;
        $this->documentRepository = $documentRepository;
        $this->objetRepository = $objetRepository;
        $this->mediaUploader = $mediaUploader;
    }

    public function handle(ConvertDocumentCommand $command)
    {
        $publication = $this->objetRepository->findOneById($command->publicationId);
        $document = $this->documentRepository->createForPublication($publication);

        $document->setTree(
            $this->converter->convert($command->file)
        );

        $this->handleMedias($publication, $document->getTree());

        $this->documentRepository->save($document);
    }

    private function handleMedias(Objet $publication, NodeInterface $node)
    {
        // @TODO Dupliquer les fichiers physiques s'ils sont présent dans plusieurs nodes

        foreach ($node->getFiles() as $file) {
            $publicPath = $this->mediaUploader->upload($file, $publication);
            Media::createForNode($node, $file->getPathname(), $publicPath);
        }

        foreach ($node->getChildrens() as $children) {
            $this->handleMedias($publication, $children);
        }
    }
}
