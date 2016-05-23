<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Model;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Model;

/**
 * ActionPlan
 *
 * @ORM\Table(name="ad_model_actionplan")
 * @ORM\Entity
 */
class ActionPlan
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
     * @var Model
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Model")
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     */
    private $model;


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

