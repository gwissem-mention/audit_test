<?php

namespace HopitalNumerique\PublicationBundle\Repository\Converter;

use Doctrine\ORM\EntityRepository;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\PublicationBundle\Entity\Converter\Document;

class DocumentRepository extends EntityRepository
{
    public function save(Document $document)
    {
        $this->_em->persist($document);
        $this->_em->flush();
    }

    /**
     * Create new Document for publication. If Document already exists, return it and delete his tree
     *
     * @param Objet $publication
     * @return Document
     */
    public function createForPublication(Objet $publication)
    {
        /** @var Document $document */
        $document = $this->findByPublication($publication);

        if (null === $document) {
            $document = new Document($publication);
        } elseif (null !== $document->getTree()) {
            $this->_em->remove($document->getTree());
            $document->unsetTree();
        }

        return $document;
    }

    /**
     * @param Objet $infradoc
     * @return null|Document
     */
    public function findByPublication(Objet $infradoc)
    {
        return $this->findOneBy(['publication' => $infradoc]);
    }
}
