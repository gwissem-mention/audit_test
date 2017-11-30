<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Activity.
 *
 * @ORM\Table(name="hn_communautepratique_groupe_activity")
 * @ORM\Entity(repositoryClass="HopitalNumerique\CommunautePratiqueBundle\Repository\ActivityRepository")
 */
class Activity
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="date_creation", type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    protected $type;

    /**
     * @var ObjectIdentity
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\CoreBundle\Entity\ObjectIdentity\ObjectIdentity", cascade={"persist"})
     */
    protected $objectIdentity;

    /**
     * Activity constructor.
     *
     * @param $type
     * @param $objectIdentity
     */
    public function __construct($type, ObjectIdentity $objectIdentity)
    {
        $this->type = $type;
        $this->objectIdentity = $objectIdentity;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdDate
     *
     * @return Activity
     */
    public function setCreatedAt($createdDate)
    {
        $this->createdAt = $createdDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Activity
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return ObjectIdentity
     */
    public function getObjectIdentity()
    {
        return $this->objectIdentity;
    }

    /**
     * @param ObjectIdentity $objectIdentity
     *
     * @return Activity
     */
    public function setObjectIdentity(ObjectIdentity $objectIdentity)
    {
        $this->objectIdentity = $objectIdentity;

        return $this;
    }
}
