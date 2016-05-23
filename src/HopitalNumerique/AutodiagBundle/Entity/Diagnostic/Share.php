<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Diagnostic;

use Doctrine\ORM\Mapping as ORM;

/**
 * Share
 *
 * @ORM\Table(name="ad_diagnostic_share")
 * @ORM\Entity
 */
class Share
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

