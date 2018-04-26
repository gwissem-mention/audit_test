<?php

namespace HopitalNumerique\CartBundle\Model\Report;

use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;

class Publication implements ItemInterface
{
    /**
     * @var Objet $object
     */
    protected $object;

    /**
     * @var Contenu[] $content
     */
    protected $content;

    /**
     * @var array $references
     */
    protected $references;

    /**
     * Publication constructor.
     *
     * @param Objet
     * @param Contenu[] $content
     * @param array $references
     */
    public function __construct(Objet $objet, $content, $references)
    {
        $this->object = $objet;
        $this->content = $content;
        $this->references = $references;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->object->getId();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->object->getTitre();
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->object->getSource();
    }

    /**
     * @return string
     */
    public function getShortResume()
    {
        return $this->object->getResumeResume();
    }

    /**
     * @return string
     */
    public function getResume()
    {
        return $this->object->getResume();
    }

    /**
     * @return string
     */
    public function getSynthesis()
    {
        return $this->object->getSynthese();
    }

    /**
     * @return ArrayCollection
     */
    public function getComments()
    {
        return $this->object->getListeCommentaires();
    }

    /**
     * @return Contenu[]
     */
    public function getContents()
    {
        return $this->content;
    }

    /**
     * @return \DateTime
     */
    public function getPublicationDate()
    {
        return $this->object->getDateCreation();
    }

    /**
     * @return \DateTime|null
     */
    public function getLastUpdateDate()
    {
        return $this->object->getDateModification();
    }

    /**
     * @return string
     */
    public function getType()
    {
        if ($this->object->isPointDur()) {
            return 'hot_point';
        }

        return 'publication';
    }

    /**
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * @return ArrayCollection|Domaine[]
     */
    public function getDomains()
    {
        return $this->object->getDomaines();
    }
}
