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
     * @ORM\Column(name="rsc_nombreClic", type="integer")
     */
    protected $nombreClic;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_user", referencedColumnName="usr_id", onDelete="CASCADE")
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
     * Set nombreClic
     *
     * @param integer $nombreClic
     * @return StatClic
     */
    public function setNombreClic($nombreClic)
    {
        $this->nombreClic = $nombreClic;

        return $this;
    }

    /**
     * Get nombreClic
     *
     * @return integer 
     */
    public function getNombreClic()
    {
        return $this->nombreClic;
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
}
