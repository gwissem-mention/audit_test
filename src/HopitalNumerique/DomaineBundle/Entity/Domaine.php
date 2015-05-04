<?php

namespace HopitalNumerique\DomaineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * Domaine
 *
 * @ORM\Table(name="hn_domaine")
 * @ORM\Entity
 */
class Domaine
{
    /**
     * @var integer
     *
     * @ORM\Column(name="dom_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="dom_url", type="string", length=255)
     */
    protected $url;

    /**
     * @ORM\ManyToOne(targetEntity="Template", cascade={"persist"})
     * @ORM\JoinColumn(name="temp_id", referencedColumnName="temp_id")
     *
     * @GRID\Column(field="template.nom", options = {"comment" = "Type de template à utiliser sur le domaine"})
     */
    protected $template;

    /**
     * @var string
     *
     * @ORM\Column(name="dom_adresse_mail_contact", type="string", length=255)
     */
    protected $adresseMailContact;


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
     * @return Domaine
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
     * Set adresseMailContact
     *
     * @param string $adresseMailContact
     * @return Domaine
     */
    public function setAdresseMailContact($adresseMailContact)
    {
        $this->adresseMailContact = $adresseMailContact;

        return $this;
    }

    /**
     * Get adresseMailContact
     *
     * @return string 
     */
    public function getAdresseMailContact()
    {
        return $this->adresseMailContact;
    }

    /**
     * Set template
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Template $template
     * @return Domaine
     */
    public function setTemplate(\HopitalNumerique\DomaineBundle\Entity\Template $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return \HopitalNumerique\DomaineBundle\Entity\Template 
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
