<?php

namespace HopitalNumerique\InterventionBundle\Entity\Intervention;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Distance.
 *
 * @ORM\Table(name="hn_intervention_forfait_transport")
 * @ORM\Entity(repositoryClass="HopitalNumerique\InterventionBundle\Repository\Intervention\ForfaitTransportRepository")
 * @UniqueEntity(fields={"distanceMaximum"}, message="Distance maximum déjà existante.")
 */
class ForfaitTransport
{
    /**
     * @var int
     *
     * @ORM\Column(name="intft_id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="intft_distance_maximum", type="smallint", nullable=false, options={"unsigned"=true})
     */
    private $distanceMaximum;

    /**
     * @var int
     *
     * @ORM\Column(name="intft_cout", type="smallint", nullable=false, options={"unsigned"=true})
     */
    private $cout;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set distanceMaximum.
     *
     * @param int $distanceMaximum
     *
     * @return ForfaitTransport
     */
    public function setDistanceMaximum($distanceMaximum)
    {
        $this->distanceMaximum = $distanceMaximum;

        return $this;
    }

    /**
     * Get distanceMaximum.
     *
     * @return int
     */
    public function getDistanceMaximum()
    {
        return $this->distanceMaximum;
    }

    /**
     * Set cout.
     *
     * @param int $cout
     *
     * @return ForfaitTransport
     */
    public function setCout($cout)
    {
        $this->cout = $cout;

        return $this;
    }

    /**
     * Get cout.
     *
     * @return int
     */
    public function getCout()
    {
        return $this->cout;
    }
}
