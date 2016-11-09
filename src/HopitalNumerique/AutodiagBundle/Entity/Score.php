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
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $min;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $max;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $complete;

    /**
     * Score constructor.
     * @param Container $container
     * @param Synthesis $synthesis
     * @param null $score
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
     * @return float
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param float $min
     *
     * @return Score
     */
    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @return float
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param float $max
     *
     * @return Score
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
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

    /**
     * @return boolean
     */
    public function isComplete()
    {
        return $this->complete;
    }

    /**
     * @param boolean $complete
     */
    public function setComplete($complete)
    {
        $this->complete = $complete;
    }
}
