<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Synthesis;

use Doctrine\ORM\Mapping as ORM;

/**
 * Diagnostic
 *
 * @ORM\Table(name="ad_synthesis_diagnostic")
 * @ORM\Entity
 */
class Diagnostic
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

