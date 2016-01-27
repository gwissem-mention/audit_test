<?php

namespace HopitalNumerique\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConnaissanceSIAmbassadeur
 *
 * @ORM\Table(name="core_user_connaissances_ambassadeur_SI")
 * @ORM\Entity(repositoryClass="HopitalNumerique\UserBundle\Repository\ConnaissanceAmbassadeurSIRepository")
 */
class ConnaissanceAmbassadeurSI
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", nullable=true, type="string", length=255)
     */
    protected $commentaire;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_domaine", referencedColumnName="ref_id")
     */
    protected $domaine;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="connaissancesAmbassadeursSI", cascade={"persist"})
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_connaissance", referencedColumnName="ref_id")
     */
    protected $connaissance;


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
     * Set commentaire
     *
     * @param string $commentaire
     * @return ConnaissanceAmbassadeur
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string 
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * Set domaine
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $domaine
     * @return ConnaissanceAmbassadeur
     */
    public function setDomaine(\HopitalNumerique\ReferenceBundle\Entity\Reference $domaine = null)
    {
        $this->domaine = $domaine;

        return $this;
    }

    /**
     * Get domaine
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getDomaine()
    {
        return $this->domaine;
    }

    /**
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     * @return ConnaissanceAmbassadeur
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
     * Set connaissance
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $connaissance
     * @return ConnaissanceAmbassadeur
     */
    public function setConnaissance(\HopitalNumerique\ReferenceBundle\Entity\Reference $connaissance = null)
    {
        $this->connaissance = $connaissance;

        return $this;
    }

    /**
     * Get connaissance
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getConnaissance()
    {
        return $this->connaissance;
    }
}
