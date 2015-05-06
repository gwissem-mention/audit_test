<?php

namespace HopitalNumerique\DomaineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * Domaine
 *
 * @ORM\Table(name="hn_domaine")
 * @ORM\Entity(repositoryClass="HopitalNumerique\DomaineBundle\Repository\DomaineRepository")
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
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\UserBundle\Entity\User", mappedBy="domaines")
     */
    protected $users;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", mappedBy="domaines")
     */
    protected $references;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->domaines = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Add users
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $users
     * @return Domaine
     */
    public function addUser(\HopitalNumerique\UserBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $users
     */
    public function removeUser(\HopitalNumerique\UserBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add references
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $references
     * @return Domaine
     */
    public function addReference(\HopitalNumerique\ReferenceBundle\Entity\Reference $references)
    {
        $this->references[] = $references;

        return $this;
    }

    /**
     * Remove references
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $references
     */
    public function removeReference(\HopitalNumerique\ReferenceBundle\Entity\Reference $references)
    {
        $this->references->removeElement($references);
    }

    /**
     * Get references
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReferences()
    {
        return $this->references;
    }
}
