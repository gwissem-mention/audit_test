<?php

namespace HopitalNumerique\RechercheParcoursBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

/**
 * GuidedSearchConfigPublicationType
 *
 * @ORM\Table(name="hn_guided_search_config_publication_type")
 * @ORM\Entity
 */
class GuidedSearchConfigPublicationType
{

    const TYPE_PRODUCTION = 'production';

    const TYPE_HOT_POINT = 'hot_point';

    const TYPE_RISK = 'risk';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", name="position")
     */
    protected $order;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $active;

    /**
     * @var RechercheParcoursGestion
     *
     * @ORM\ManyToOne(targetEntity="RechercheParcoursGestion", inversedBy="publicationsType")
     * @ORM\JoinColumn(referencedColumnName="rrpg_id", nullable=false)
     */
    protected $guidedSearchConfig;

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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return GuidedSearchConfigPublicationType
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     *
     * @return GuidedSearchConfigPublicationType
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return GuidedSearchConfigPublicationType
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return RechercheParcoursGestion
     */
    public function getGuidedSearchConfig()
    {
        return $this->guidedSearchConfig;
    }

    /**
     * @param RechercheParcoursGestion $guidedSearchConfig
     *
     * @return GuidedSearchConfigPublicationType
     */
    public function setGuidedSearchConfig(RechercheParcoursGestion $guidedSearchConfig)
    {
        $this->guidedSearchConfig = $guidedSearchConfig;

        return $this;
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_HOT_POINT => self::TYPE_HOT_POINT,
            self::TYPE_PRODUCTION => self::TYPE_PRODUCTION,
            self::TYPE_RISK => self::TYPE_RISK,
        ];
    }
}
