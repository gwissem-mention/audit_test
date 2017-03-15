<?php

namespace HopitalNumerique\ReferenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ReferenceCode
 * @ORM\Table(name="hn_reference_code")
 * @ORM\Entity()
 */
class ReferenceCode
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Le libellé ne peut pas être vide.")
     */
    private $label;

    /**
     * @var Reference
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumn(name="reference", referencedColumnName="ref_id")
     */
    private $reference;

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
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return ReferenceCode
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Reference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param Reference $reference
     *
     * @return ReferenceCode
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }
}
