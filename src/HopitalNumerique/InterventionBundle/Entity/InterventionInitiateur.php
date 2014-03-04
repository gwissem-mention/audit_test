<?php

namespace HopitalNumerique\InterventionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entité de l'initiateur d'une intervention.
 *
 * @ORM\Table(name="hn_intervention_initiateur")
 * @ORM\Entity
 */
class InterventionInitiateur
{
    /**
     * @var integer
     *
     * @ORM\Column(columnDefinition="TINYINT(3) UNSIGNED NOT NULL", name="intervinit_id", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="intervinit_type", type="string", length=32, nullable=false)
     */
    private $type;


}
