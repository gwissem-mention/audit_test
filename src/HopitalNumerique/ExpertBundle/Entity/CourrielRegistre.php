<?php
namespace HopitalNumerique\ExpertBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * CourrielRegistre
 *
 * @ORM\Table(name="hn_expert_activite_courriel_registre")
 * @ORM\Entity()
 */
class CourrielRegistre
{
    /**
     * @var integer
     *
     * @ORM\Column(name="coreg_id", type="integer", options={"unsigned"=true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="coreg_destinataire", type="string", length=255)
     */
    private $destinataire;

    /**
     * @var \HopitalNumerique\UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE", nullable=false)
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="coreg_date_creation", type="datetime", nullable=false)
     */
    private $dateCreation;


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
     * Set destinataire
     *
     * @param string $destinataire
     *
     * @return CourrielRegistre
     */
    public function setDestinataire($destinataire)
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    /**
     * Get destinataire
     *
     * @return string
     */
    public function getDestinataire()
    {
        return $this->destinataire;
    }

    /**
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     *
     * @return CourrielRegistre
     */
    public function setUser(\HopitalNumerique\UserBundle\Entity\User $user)
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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return CourrielRegistre
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }
}
