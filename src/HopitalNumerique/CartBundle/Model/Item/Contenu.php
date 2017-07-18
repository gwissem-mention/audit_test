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

    /**
     * @inheritdoc
     */
    public function getParentsTitle()
    {
        $parents  = [];
        $parent = $this->content->getParent();
        while (!is_null($parent)) {
            $parents[] = $parent;

            $parent = $parent->getParent();
        }
        $parents = array_reverse($parents);

        $titles = [];
        $codes = [];
        foreach ($parents as $k => $parent) {
            $codes[] = $parent->getOrder();
            $titles[] = implode('.', $codes) . '. ' . $parent->getTitre();
        }

        return $titles;
    }

    /**
     * @return string
     */
    private function getTitleCode()
    {
        $parent = $this->content->getParent();
        $codes = [];
        while (!is_null($parent)) {
            $codes[] = $parent->getOrder();

            $parent = $parent->getParent();
        }

        $codes = array_reverse($codes);
        $codes[] = $this->content->getOrder();
        $parentsCode = implode('.', $codes);

        return sprintf('%s.', $parentsCode);
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return sprintf('%s %s', $this->getTitleCode(), $this->content->getTitre());
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
