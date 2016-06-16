<?php

namespace HopitalNumerique\AutodiagBundle\Entity\AutodiagEntry;

use Doctrine\ORM\Mapping as ORM;

/**
 * Value
 *
 * @ORM\Table(name="ad_diagnostic_value")
 * @ORM\Entity
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}

