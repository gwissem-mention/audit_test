<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Autodiag\ActionPlan;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\ActionPlan;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Option.
 *
 * @ORM\Table(name="ad_autodiag_actionplan_link")
 * @ORM\Entity
 */
class Link
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var ActionPlan
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\ActionPlan", inversedBy="links")
     * @ORM\JoinColumn(name="actionplan_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $actionPlan;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(max="255")
     */
    private $description;

    /**
     * Link constructor.
     *
     * @param ActionPlan $actionPlan
     * @param            $url
     * @param null       $description
     */
    public function __construct(ActionPlan $actionPlan, $url, $description = null)
    {
        $this->actionPlan = $actionPlan;
        $this->url = $url;
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ActionPlan
     */
    public function getActionPlan()
    {
        return $this->actionPlan;
    }

    /**
     * @param ActionPlan $actionPlan
     *
     * @return Link
     */
    public function setActionPlan(ActionPlan $actionPlan)
    {
        $this->actionPlan = $actionPlan;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return Link
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Link
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
