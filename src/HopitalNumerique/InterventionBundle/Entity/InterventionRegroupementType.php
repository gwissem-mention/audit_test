<?php

namespace HopitalNumerique\InterventionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entité d'un type de regroupement d'intervention.
 *
 * @ORM\Table(name="hn_intervention_regroupement_type")
 * @ORM\Entity
 */
class InterventionRegroupementType
{
    /**
     * @var integer
     *
     * @ORM\Column(columnDefinition="TINYINT(3) UNSIGNED NOT NULL", name="intervregtyp_id", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="intervregtyp_libelle", type="string", length=32, nullable=false)
     */
    private $libelle;

    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return InterventionRegroupementType
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string 
     */
    public function getLibelle()
    {
        return $this->libelle;
    }
}
