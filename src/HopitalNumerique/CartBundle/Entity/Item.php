<?php

namespace HopitalNumerique\CartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\DomaineBundle\Entity\Domaine;

/**
 * @ORM\MappedSuperclass
 */
class Item
{
    const CONTENT_TYPE = 'contenu';
    const HOT_POINT_TYPE = 'hot_point';
    const OBJECT_TYPE = 'objet';
    const FORUM_TOPIC_TYPE = 'forum_topic';
    const AUTODIAG_CHAPTER_TYPE = 'autodiag_chapter';
    const CDP_GROUP_TYPE = 'cdp_group';
    const CDP_DISCUSSION_TYPE = 'cdp_discussion';
    const PERSON_TYPE = 'person';
    const GUIDED_SEARCH_TYPE = 'guided_search';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    protected $objectType;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected $objectId;

    /**
     * @var Domaine
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\DomaineBundle\Entity\Domaine")
     * @ORM\JoinColumn(referencedColumnName="dom_id")
     */
    protected $domain;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @param string $objectType
     *
     * @return Item
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;

        return $this;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     *
     * @return Item
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * @return Domaine
     */
    public function getDomain()
    {
        return $this->domain;
    }

    public function __clone()
    {
        $this->id = null;
    }
}
