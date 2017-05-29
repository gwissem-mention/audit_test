<?php

namespace HopitalNumerique\AutodiagBundle\Entity\Autodiag;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag;
use HopitalNumerique\AutodiagBundle\Entity\Autodiag\ActionPlan\Link;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ActionPlan.
 *
 * @ORM\Table(name="ad_autodiag_actionplan")
 * @ORM\Entity
 */
class ActionPlan
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

    // ----------------------------------------------------------------------------------------------------------------
    // @TODO: Supprimer après la migration ----------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------

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
     * @deprecated
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @deprecated
     *
     * @param string $link
     *
     * @return ActionPlan
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @deprecated
     *
     * @return mixed
     */
    public function getLinkDescription()
    {
        return $this->linkDescription;
    }

    /**
     * @deprecated
     *
     * @param mixed $linkDescription
     *
     * @return ActionPlan
     */
    public function setLinkDescription($linkDescription)
    {
        $this->linkDescription = $linkDescription;

        return $this;
    }

    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------

    /**
     * @var Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="HopitalNumerique\AutodiagBundle\Entity\Autodiag\ActionPlan\Link",
     *     mappedBy="actionPlan",
     *     cascade={"persist", "remove", "detach"},
     *     orphanRemoval=true,
     *     fetch="EAGER"
     * )
     * @Assert\Valid()
     */
    private $links;

    /**
     * @var bool
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

    /**
     * ActionPlan constructor.
     */
    private function __construct()
    {
        $this->links = new ArrayCollection();
    }

    /**
     * @param Autodiag  $autodiag
     * @param Container $container
     * @param           $value
     *
     * @return ActionPlan
     */
    public static function createForContainer(Autodiag $autodiag, Container $container, $value)
    {
        $actionPlan = new self();
        $actionPlan->setAutodiag($autodiag);
        $actionPlan->setContainer($container);
        $actionPlan->setValue($value);

        return $actionPlan;
    }

    /**
     * @param Autodiag  $autodiag
     * @param Attribute $attribute
     * @param           $value
     *
     * @return ActionPlan
     */
    public static function createForAttribute(Autodiag $autodiag, Attribute $attribute, $value)
    {
        $actionPlan = new self();
        $actionPlan->setAutodiag($autodiag);
        $actionPlan->setAttribute($attribute);
        $actionPlan->setValue($value);

        return $actionPlan;
    }

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
     * @return ArrayCollection
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param Link $link
     *
     * @return ActionPlan
     */
    public function addLink(Link $link)
    {
        $this->links[] = $link;

        return $this;
    }

    /**
     * @param $links
     *
     * @return ActionPlan
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
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
