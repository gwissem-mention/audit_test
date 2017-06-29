<?php

namespace HopitalNumerique\CartBundle\Model\Item;

use HopitalNumerique\ObjetBundle\Entity\Contenu as ContenuEntity;

class Contenu extends Item
{
    /**
     * @var ContenuEntity $content
     */
    protected $content;

    /**
     * Contenu constructor.
     *
     * @param ContenuEntity $content
     */
    public function __construct(ContenuEntity $content)
    {
        $this->content = $content;
    }

    /**
     * @return ContenuEntity
     */
    public function getObject()
    {
        return $this->content;
    }

    public function getParentTitle()
    {
        return $this->content->getObjet()->getTitre();
    }

    public function getTitle()
    {
        return $this->content->getTitre();
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return \HopitalNumerique\CartBundle\Entity\Item::CONTENT_TYPE;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->content->getId();
    }

    /**
     * @inheritdoc
     */
    public function getRoute()
    {
        return 'hopital_numerique_publication_publication_contenu';
    }

    /**
     * @inheritdoc
     */
    public function getRouteParameters()
    {
        return [
            'id' => $this->content->getObjet()->getId(),
            'alias' => $this->content->getObjet()->getAlias(),
            'idc' => $this->content->getId(),
            'aliasc' => $this->content->getAlias(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getDomains()
    {
        return $this->content->getObjet()->getDomaines();
    }
}
