<?php

namespace HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute;
use HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;

/**
 * Value
 *
 * @ORM\Table(name="ad_entry_value")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\AutodiagEntry\ValueRepository")
 */
class Value
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Attribute
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $attribute;

    /**
     * @var AutodiagEntry
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry", inversedBy="values")
     * @ORM\JoinColumn(name="autodiagentry_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $entry;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $valid;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Attribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param Attribute $attribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * @return AutodiagEntry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param AutodiagEntry $entry
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;
        $this->entry->addValue($this);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function setNotConcerned()
    {
        $this->setValue(null);
        $this->setValid(true);
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @param boolean $valid
     * @return Value
     */
    public function setValid($valid)
    {
        $this->valid = $valid;

        return $this;
    }

    public function __clone()
    {
        $this->id = null;
    }
}
