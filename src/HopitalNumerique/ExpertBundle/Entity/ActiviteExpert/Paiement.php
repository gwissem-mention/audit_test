<?php
namespace HopitalNumerique\ExpertBundle\Entity\ActiviteExpert;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Paiement.
 *
 * @ORM\Entity()
 * @ORM\Table(name="hn_expert_activite_paiement", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="ACTIVITEEXPERT_EXPERT", columns={ "exp_id", "usr_id" })
 * })
 */
class Paiement
{
    /**
     * @var integer
     *
     * @ORM\Column(name="eap_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ExpertBundle\Entity\ActiviteExpert")
     * @ORM\JoinColumn(name="exp_id", referencedColumnName="exp_id", nullable=false)
     */
    private $activiteExpert;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", nullable=false)
     */
    private $expert;

    /**
     * @var integer
     *
     * @ORM\Column(name="eap_vacations_count", type="smallint", nullable=false, options={"unsigned"=true})
     * @Assert\Range(
     *      min = 0,
     *      minMessage = "Veuillez choisir un nombre positif"
     * )
     */
    private $vacationsCount;


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
     * Set vacationsCount
     *
     * @param string $vacationsCount
     *
     * @return Paiement
     */
    public function setVacationsCount($vacationsCount)
    {
        $this->vacationsCount = $vacationsCount;

        return $this;
    }

    /**
     * Get vacationsCount
     *
     * @return string
     */
    public function getVacationsCount()
    {
        return $this->vacationsCount;
    }

    /**
     * Set activiteExpert
     *
     * @param \HopitalNumerique\ExpertBundle\Entity\ActiviteExpert $activiteExpert
     *
     * @return Paiement
     */
    public function setActiviteExpert(\HopitalNumerique\ExpertBundle\Entity\ActiviteExpert $activiteExpert)
    {
        $this->activiteExpert = $activiteExpert;

        return $this;
    }

    /**
     * Get activiteExpert
     *
     * @return \HopitalNumerique\ExpertBundle\Entity\ActiviteExpert
     */
    public function getActiviteExpert()
    {
        return $this->activiteExpert;
    }

    /**
     * Set expert
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $expert
     *
     * @return Paiement
     */
    public function setExpert(\HopitalNumerique\UserBundle\Entity\User $expert)
    {
        $this->expert = $expert;

        return $this;
    }

    /**
     * Get expert
     *
     * @return \HopitalNumerique\UserBundle\Entity\User
     */
    public function getExpert()
    {
        return $this->expert;
    }
}
