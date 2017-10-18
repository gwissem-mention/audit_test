<?php

namespace HopitalNumerique\CoreBundle\Entity\ObjectIdentity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Util\ClassUtils;

/**
 * @ORM\Entity(repositoryClass="HopitalNumerique\CoreBundle\Repository\ObjectIdentity\ObjectIdentityRepository")
 * @ORM\Table(name="object_identity")
 */
class ObjectIdentity
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $class;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $objectId;

    /**
     * @var mixed
     */
    protected $object;

    /**
     * ObjectIdentity constructor.
     *
     * @param string $class
     * @param string $objectId
     * @param mixed $object
     */
    public function __construct($class, $objectId, $object = null)
    {
        $this->id = sha1($class.'::'.$objectId);
        $this->class = $class;
        $this->objectId = $objectId;
        $this->object = $object;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param mixed $object
     *
     * @return ObjectIdentity
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @param mixed $domainObject
     *
     * @return ObjectIdentity
     */
    public static function createFromDomainObject($domainObject)
    {
        $class = ClassUtils::getClass($domainObject);
        if (method_exists($domainObject, 'getId')) {
            return new self($class, $domainObject->getId(), $domainObject);
        } elseif ($domainObject instanceof ObjectIdentityInterface) {
            return new self($class, $domainObject->getObjectIdentityId(), $domainObject);
        }

        throw new \LogicException();
    }
}
