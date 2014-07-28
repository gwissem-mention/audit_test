<?php

namespace HopitalNumerique\RechercheParcoursBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MaitriseUser
 *
 * @ORM\Table(name="hn_recherche_maitrise_user")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheParcoursBundle\Repository\MaitriseUserRepository")
 */
class MaitriseUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="rmu_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="rmu_pourcentageMaitrise", type="integer", options = {"comment" = "Pourcentage de maitrise du point dur par l utilisateur."})
     */
    protected $pourcentageMaitrise;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="RechercheParcoursDetails", inversedBy="maitriseUsers")
     * @ORM\JoinColumn(name="rrpd_id", referencedColumnName="rrpd_id")
     */
    protected $rechercheParcoursDetails;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_user", referencedColumnName="usr_id")
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

    public function getRechercheParcoursDetails()
    {
        return $this->rechercheParcoursDetails;
    }
    
    public function setRechercheParcoursDetails( RechercheParcoursDetails $rechercheParcoursDetails )
    {
        $this->rechercheParcoursDetails = $rechercheParcoursDetails;
    }

    /**
     * Set pourcentageMaitrise
     *
     * @param integer $pourcentageMaitrise
     * @return MaitriseUser
     */
    public function setPourcentageMaitrise($pourcentageMaitrise)
    {
        $this->pourcentageMaitrise = $pourcentageMaitrise;

        return $this;
    }

    /**
     * Get pourcentageMaitrise
     *
     * @return integer 
     */
    public function getPourcentageMaitrise()
    {
        return $this->pourcentageMaitrise;
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
