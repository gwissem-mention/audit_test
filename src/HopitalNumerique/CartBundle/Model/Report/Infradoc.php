<?php

namespace HopitalNumerique\CartBundle\Model\Report;

use HopitalNumerique\ObjetBundle\Entity\Contenu;

class Infradoc implements ItemInterface
{
    /**
     * @var Contenu $content
     */
    protected $content;

    /**
     * @var array $references
     */
    protected $references;

    /**
     * Infradoc constructor.
     *
     * @param Contenu $content
     * @param array $references
     */
    public function __construct(Contenu $content, $references)
    {
        $this->content = $content;
        $this->references = $references;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->content->getId();
    }

    /**
     * @return int
     */
    public function getObjetId()
    {
        return $this->content->getObjet()->getId();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return sprintf('%s %s', $this->getTitleCode(), $this->content->getTitre());
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdateDate()
    {
        return $this->content->getDateModification();
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->content->getDateCreation();
    }

    /**
     * @return array
     */
    public function getParentsTitles()
    {
        $parents  = [];
        $parent = $this->content->getParent();
        while (null !== $parent) {
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

        array_unshift($titles, $this->content->getObjet()->getTitre());

        return $titles;
    }

    /**
     * @return string
     */
    private function getTitleCode()
    {
        $parent = $this->content->getParent();
        $codes = [];
        while (null !== $parent) {
            $codes[] = $parent->getOrder();

            $parent = $parent->getParent();
        }

        $codes = array_reverse($codes);
        $codes[] = $this->content->getOrder();
        $parentsCode = implode('.', $codes);

        return sprintf('%s.', $parentsCode);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->content->getChildren();
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content->getContenu();
    }

    /**
     * @return string
     */
    public function getShortContent()
    {
        return $this->content->getContenu();
    }

    public function getComments()
    {
        return $this->content->getListeCommentaires();
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return 'infradoc';
    }

    /**
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }
}
