<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Autodiag;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ActionPlan
 *
 * @ORM\Table(name="ad_autodiag_actionplan")
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
     * @var float
     * @ORM\Column(type="float")
     * @Assert\NotNull();
     */
    private $value;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $link;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(max="255")
     */
    private $linkDescription;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     * @Assert\NotNull()
     */
    private $visible = true;

    /**
     * @var Autodiag
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag", inversedBy="actionPlans")
     * @ORM\JoinColumn(name="autodiag_id", referencedColumnName="id")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $autodiag;

    /**
     * @var Attribute
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\Attribute")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $attribute;

    /**
     * @var Container
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\Container")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $container;

    public static function createForContainer(Autodiag $autodiag, Container $container, $value)
    {
        $actionPlan = new ActionPlan();
        $actionPlan->setAutodiag($autodiag);
        $actionPlan->setContainer($container);
        $actionPlan->setValue($value);
        return $actionPlan;
    }

    public static function createForAttribute(Autodiag $autodiag, Attribute $attribute, $value)
    {
        $actionPlan = new ActionPlan();
        $actionPlan->setAutodiag($autodiag);
        $actionPlan->setAttribute($attribute);
        $actionPlan->setValue($value);
        return $actionPlan;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     */
    public function setValue($value)
    {
        $this->value = $value;
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
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getLinkDescription()
    {
        return $this->linkDescription;
    }

    /**
     * @param string $linkDescription
     */
    public function setLinkDescription($linkDescription)
    {
        $this->linkDescription = $linkDescription;
    }

    /**
     * @return boolean
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @param boolean $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    /**
     * @return Autodiag
     */
    public function getAutodiag()
    {
        return $this->autodiag;
    }

    /**
     * @param Autodiag $autodiag
     */
    public function setAutodiag($autodiag)
    {
        $this->autodiag = $autodiag;
    }

    /**
     * @return mixed
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param mixed $attribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * @return mixed
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param mixed $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }
}
