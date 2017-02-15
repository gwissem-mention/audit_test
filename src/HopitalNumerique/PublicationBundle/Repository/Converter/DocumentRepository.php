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
     * Create new Document for infradoc. If Document already exists, return it and delete his tree
     *
     * @param Objet $infradoc
     * @return Document
     */
    public function createForInfradoc(Objet $infradoc)
    {
        /** @var Document $document */
        $document = $this->findByPublication($infradoc);

        if (null === $document) {
            $document = new Document($infradoc);
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
