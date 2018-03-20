<?php

namespace HopitalNumerique\PublicationBundle\Service\Converter;

use Doctrine\ORM\EntityManager;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\PublicationBundle\Entity\Converter\Document;
use HopitalNumerique\PublicationBundle\Model\Converter\Document\ExcludeNode;
use HopitalNumerique\PublicationBundle\Model\Converter\Document\SquashableNode;
use HopitalNumerique\PublicationBundle\Service\Converter\Content\TargetBlank;
use HopitalNumerique\PublicationBundle\Service\Converter\Node\NodeParser;
use HopitalNumerique\PublicationBundle\Service\Converter\Node\TreeSquasher;
use Symfony\Component\DomCrawler\Crawler;

class ContentGenerator
{
    const DEFAULT_TITLE = "empty_title";

    /**
     * @var NodeParser
     */
    protected $nodeParser;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var TargetBlank
     */
    protected $targetBlank;

    /**
     * @var MediaUploader
     */
    protected $mediaUploader;

    /**
     * ContentGenerator constructor.
     * @param NodeParser $nodeParser
     */
    public function __construct(NodeParser $nodeParser, EntityManager $entityManager, TargetBlank $targetBlank, MediaUploader $mediaUploader)
    {
        $this->nodeParser = $nodeParser;
        $this->entityManager = $entityManager;
        $this->targetBlank = $targetBlank;
        $this->mediaUploader = $mediaUploader;
    }

    /**
     * Generate Object infradoc from a Document
     *
     * @param Document $document
     */
    public function generateFromDocument(Document $document)
    {
        $rootNode = $document->getTree();

        $this->clearMedias($rootNode);

        $rootNode = new ExcludeNode($rootNode);
        $rootNode->exclude();

        $rootNode = new SquashableNode($rootNode);
        $rootNode->squash();

        $this->handleNode($document, $document->getTree());

        $this->targetBlank->parse($document->getPublication());

        $document->getPublication()->setIsInfraDoc(true);
        $this->entityManager->flush($document->getPublication());

        $this->entityManager->remove($document->getTree());
        $this->entityManager->remove($document);
        $this->entityManager->flush($document);
    }

    /**
     * @param Document $document
     * @param Document\NodeInterface $node
     * @param int $order
     *
     * @return Contenu|null
     */
    protected function handleNode(Document $document, Document\NodeInterface $node, $order = 1)
    {
        $this->nodeParser->parse($node);

        $infradoc = $this->nodeToInfradoc($node, $document->getPublication());
        if (null !== $infradoc) {
            $infradoc->setOrder($order);
        }

        $order = 1;
        foreach ($node->getChildrens() as $children) {
            $infraChild = $this->handleNode($document, $children, $order++);
            if (null !== $infradoc) {
                $infraChild->setParent($infradoc);
            }
        }

        return $infradoc;
    }

    /**
     * Transform a Node to an Object Content (infradoc)
     *
     * @param Document\NodeInterface $node
     * @param Objet $object
     *
     * @return Contenu|null
     */
    protected function nodeToInfradoc(Document\NodeInterface $node, Objet $object)
    {
        if (0 === $node->getDeep()) {
            return null;
        }

        $infradoc = new Contenu();
        $infradoc
            ->setObjet($object)
            ->setTitre(null !== $node->getTitle() ? $node->getTitle() : self::DEFAULT_TITLE)
            ->setContenu($node->getContent())
        ;

        foreach ($object->getDomaines() as $domaine) {
            $infradoc->addDomaine($domaine);
        }

        $object->addContenus($infradoc);

        $this->entityManager->persist($infradoc);

        return $infradoc;
    }

    /**
     * Delete medias for excluded nodes
     *
     * @param Document\NodeInterface $node
     */
    protected function clearMedias(Document\NodeInterface $node)
    {
        if ($node->isExcluded()) {
            foreach ($node->getMedias() as $media) {
                $this->mediaUploader->removeMedia($media);
            }
        }

        foreach ($node->getChildrens() as $children) {
            $this->clearMedias($children);
        }
    }
}
