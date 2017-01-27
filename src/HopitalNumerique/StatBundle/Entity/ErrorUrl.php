<?php

namespace HopitalNumerique\StatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Contenu;
use HopitalNumerique\ObjetBundle\Entity\Objet;

/**
 * ErrorUrl
 *
 * @ORM\Table(name="hn_statistics_error_url")
 * @ORM\Entity(repositoryClass="HopitalNumerique\StatBundle\Repository\ErrorUrlRepository")
 */
class ErrorUrl
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="checked_url", type="string", length=1024)
     */
    private $checkedUrl;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $state;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $code;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_check_date", type="datetime", nullable=true)
     */
    private $lastCheckDate;

    /**
     * @var Objet
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ObjetBundle\Entity\Objet")
     * @ORM\JoinColumn(referencedColumnName="obj_id")
     */
    private $object;

    /**
     * @var Contenu
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ObjetBundle\Entity\Contenu")
     * @ORM\JoinColumn(referencedColumnName="con_id", nullable=true)
     */
    private $content;

    /**
     * @var Domaine
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\DomaineBundle\Entity\Domaine")
     * @ORM\JoinColumn(referencedColumnName="dom_id")
     */
    private $domain;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return ErrorUrl
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getCheckedUrl()
    {
        return $this->checkedUrl;
    }

    /**
     * @param string $checkedUrl
     *
     * @return ErrorUrl
     */
    public function setCheckedUrl($checkedUrl)
    {
        $this->checkedUrl = $checkedUrl;

        return $this;
    }

    /**
     * @return bool
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param bool $state
     *
     * @return ErrorUrl
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     *
     * @return ErrorUrl
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastCheckDate()
    {
        return $this->lastCheckDate;
    }

    /**
     * @param \DateTime $lastCheckDate
     *
     * @return ErrorUrl
     */
    public function setLastCheckDate($lastCheckDate)
    {
        $this->lastCheckDate = $lastCheckDate;

        return $this;
    }

    /**
     * @return Objet
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param Objet $object
     *
     * @return ErrorUrl
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return Contenu
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param Contenu $content
     *
     * @return ErrorUrl
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Domaine
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param Domaine $domain
     *
     * @return ErrorUrl
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }
}
