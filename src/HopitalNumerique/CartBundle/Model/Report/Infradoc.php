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
     * @return string
     */
    public function getTitle()
    {
        return $this->content->getTitre();
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
        $titles = [];
        if (!is_null($this->content->getParent())) {
            $titles = array_merge($titles, $this->getParentsTitle($this->content->getParent()));
        }
        $titles[] = $this->content->getObjet()->getTitre();
        asort($titles);

        return $titles;
    }

    /**
     * @param Contenu $contenu
     *
     * @return array
     */
    public function getParentsTitle(Contenu $contenu)
    {
        $titles = [$contenu->getTitre()];

        if (!is_null($contenu->getParent())) {
            $titles = array_merge($titles, $this->getParentsTitle($contenu->getParent()));
        }

        return $titles;
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
        return html_entity_decode(strip_tags($this->content->getContenu()), 2 | 0, 'UTF-8');
    }

    public function getComments()
    {
        return $this->content->getListeCommentaires();
    }

    /**
     * @return string
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
