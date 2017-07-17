<?php

namespace HopitalNumerique\CartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
    const PERSON_TYPE = 'person';

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

    public function __clone()
    {
        $this->id = null;
    }
}
