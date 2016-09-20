<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Score
 * @package HopitalNumerique\AutodiagBundle\Entity
 *
 * @ORM\Table(name="ad_score")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\ScoreRepository")
 */
class Score
{
    /**
     * @var Container
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container")
     * @ORM\JoinColumn(name="container_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $container;

    /**
     * @var Synthesis
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Synthesis")
     * @ORM\JoinColumn(name="synthesis_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $synthesis;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $score;

    /**
     * Score constructor.
     */
    public function __construct(Container $container, Synthesis $synthesis, $score = null)
    {
        $this->container = $container;
        $this->synthesis = $synthesis;
        $this->score = $score;
    }

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param float $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return Synthesis
     */
    public function getSynthesis()
    {
        return $this->synthesis;
    }
}
