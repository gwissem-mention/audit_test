<?php

namespace HopitalNumerique\CoreBundle\Entity\ObjectIdentity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Util\ClassUtils;

/**
 * @ORM\Entity(repositoryClass="HopitalNumerique\CoreBundle\Repository\ObjectIdentity\RelationRepository")
 * @ORM\Table(name="object_identity_relation")
 */
class Relation
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", unique=true)
     * @ORM\GeneratedValue(strategy="AUTO"))
     */
    protected $id;

    /**
     * @var ObjectIdentity
     *
     * @ORM\ManyToOne(targetEntity="ObjectIdentity", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $sourceObjectIdentity;

    /**
     * @var ObjectIdentity
     *
     * @ORM\ManyToOne(targetEntity="ObjectIdentity", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $targetObjectIdentity;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", name="position")
     */
    protected $order = 0;

    /**
     * Relation constructor.
     *
     * @param ObjectIdentity $sourceObjectIdentity
     * @param ObjectIdentity $targetObjectIdentity
     * @param int $order
     */
    public function __construct(ObjectIdentity $sourceObjectIdentity, ObjectIdentity $targetObjectIdentity, $order = 0)
    {
        $this->sourceObjectIdentity = $sourceObjectIdentity;
        $this->targetObjectIdentity = $targetObjectIdentity;
        $this->order = $order;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ObjectIdentity
     */
    public function getSourceObjectIdentity()
    {
        return $this->sourceObjectIdentity;
    }

    /**
     * @return ObjectIdentity
     */
    public function getTargetObjectIdentity()
    {
        return $this->targetObjectIdentity;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     *
     * @return Relation
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }
}
