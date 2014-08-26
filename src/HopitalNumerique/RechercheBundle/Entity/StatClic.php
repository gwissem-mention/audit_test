<?php

namespace HopitalNumerique\RechercheBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StatClic
 *
 * @ORM\Table(name="hn_recherche_expBesoin_statclic")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheBundle\Repository\StatClicRepository")
 */
class StatClic
{
    /**
     * @var integer
     *
     * @ORM\Column(name="rsc_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="rsc_dateClic", type="datetime")
     */
    protected $dateClic;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses")
     * @ORM\JoinColumn(name="expbr_id", referencedColumnName="expbr_id", onDelete="CASCADE")
     */
    protected $reponse;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_user", referencedColumnName="usr_id", nullable=true, onDelete="CASCADE")
     */
    protected $user;


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
     * Set dateClic
     *
     * @param \DateTime $dateClic
     * @return StatClic
     */
    public function setDateClic($dateClic)
    {
        $this->dateClic = $dateClic;

        return $this;
    }

    /**
     * Get dateClic
     *
     * @return \DateTime 
     */
    public function getDateClic()
    {
        return $this->dateClic;
    }

    /**
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     * @return MaitriseUser
     */
    public function setUser(\HopitalNumerique\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }
    
    /**
     * Get user
     *
     * @return \HopitalNumerique\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set reponse
     *
     * @param \HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses $reponse
     * @return StatClic
     */
    public function setReponse(\HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses $reponse = null)
    {
        $this->reponse = $reponse;

        return $this;
    }

    /**
     * Get reponse
     *
     * @return \HopitalNumerique\RechercheBundle\Entity\ExpBesoinReponses 
     */
    public function getReponse()
    {
        return $this->reponse;
    }
}
