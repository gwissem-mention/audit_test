<?php

namespace HopitalNumerique\ReferenceBundle\Entity\Reference;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Hobby.
 *
 * @ORM\Table(name="hn_reference_hobby", uniqueConstraints={@ORM\UniqueConstraint(name="unique_label", columns={"hob_label"})})
 * @ORM\Entity(repositoryClass="HopitalNumerique\ReferenceBundle\Repository\HobbyRepository")
 */
class Hobby
{
    /**
     * @var int
     *
     * @ORM\Column(name="hob_id", type="integer", options={"unsigned"=true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="hob_label", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $label;

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
     * @return Hobby
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }
}
