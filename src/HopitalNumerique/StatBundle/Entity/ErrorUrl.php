<?php

namespace HopitalNumerique\StatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ErrorUrl
 *
 * @ORM\Table(name="hn_statistiques_erreurs_url")
 * @ORM\Entity
 */
class ErrorUrl
{
    /**
     * @var integer
     *
     * @ORM\Column(name="sturl_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="sturl_url", type="string", length=255)
     */
    private $url;

    /**
     * @var boolean
     *
     * @ORM\Column(name="sturl_ok", type="boolean")
     */
    private $ok;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sturl_dateDernierCheck", type="datetime", nullable=true)
     */
    private $dateDernierCheck;


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
     * Set url
     *
     * @param string $url
     * @return ErrorUrl
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set ok
     *
     * @param boolean $ok
     * @return ErrorUrl
     */
    public function setOk($ok)
    {
        $this->ok = $ok;

        return $this;
    }

    /**
     * Get ok
     *
     * @return boolean 
     */
    public function getOk()
    {
        return $this->ok;
    }

    /**
     * Set dateDernierCheck
     *
     * @param \DateTime $dateDernierCheck
     * @return ErrorUrl
     */
    public function setDateDernierCheck($dateDernierCheck)
    {
        $this->dateDernierCheck = $dateDernierCheck;

        return $this;
    }

    /**
     * Get dateDernierCheck
     *
     * @return \DateTime 
     */
    public function getDateDernierCheck()
    {
        return $this->dateDernierCheck;
    }
}
