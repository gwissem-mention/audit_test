<?php

namespace HopitalNumerique\PaiementBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Remboursement.
 *
 * @ORM\Table(name="hn_facture_remboursement")
 * @ORM\Entity(repositoryClass="HopitalNumerique\PaiementBundle\Repository\RemboursementRepository")
 */
class Remboursement
{
    /**
     * @var int
     *
     * @ORM\Column(name="rem_id", type="integer", options = {"comment" = "ID du remboursement"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="rem_supplement", type="integer", nullable = true, options = {"comment" = "Forfait formation"})
     */
    private $supplement;

    /**
     * @var int
     *
     * @ORM\Column(name="rem_repas", type="integer", options = {"comment" = "Forfait repas du remboursement"})
     */
    private $repas;

    /**
     * @var int
     *
     * @ORM\Column(name="rem_gestion", type="integer", options = {"comment" = "Frais de gestion administrative du remboursement"})
     */
    private $gestion;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_region", referencedColumnName="ref_id", onDelete="CASCADE")
     */
    protected $region;

    /**
     * @var \HopitalNumerique\UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="referent_id", referencedColumnName="usr_id", nullable=true, onDelete="SET NULL")
     */
    private $referent;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set supplement.
     *
     * @param int $supplement
     *
     * @return Remboursement
     */
    public function setSupplement($supplement)
    {
        $this->supplement = $supplement;

        return $this;
    }

    /**
     * Get supplement.
     *
     * @return int
     */
    public function getSupplement()
    {
        return $this->supplement;
    }

    /**
     * Set repas.
     *
     * @param int $repas
     *
     * @return Remboursement
     */
    public function setRepas($repas)
    {
        $this->repas = $repas;

        return $this;
    }

    /**
     * Get repas.
     *
     * @return int
     */
    public function getRepas()
    {
        return $this->repas;
    }

    /**
     * Set gestion.
     *
     * @param int $gestion
     *
     * @return Remboursement
     */
    public function setGestion($gestion)
    {
        $this->gestion = $gestion;

        return $this;
    }

    /**
     * Get gestion.
     *
     * @return int
     */
    public function getGestion()
    {
        return $this->gestion;
    }

    /**
     * Get region.
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set region.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $region
     */
    public function setRegion(\HopitalNumerique\ReferenceBundle\Entity\Reference $region)
    {
        $this->region = $region;
    }

    /**
     * Set referent.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $referent
     *
     * @return Remboursement
     */
    public function setReferent(\HopitalNumerique\UserBundle\Entity\User $referent = null)
    {
        $this->referent = $referent;

        return $this;
    }

    /**
     * Get referent.
     *
     * @return \HopitalNumerique\UserBundle\Entity\User
     */
    public function getReferent()
    {
        return $this->referent;
    }
}
