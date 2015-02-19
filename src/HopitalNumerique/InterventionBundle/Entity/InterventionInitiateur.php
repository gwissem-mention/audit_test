<?php

namespace HopitalNumerique\InterventionBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité de l'initiateur d'une intervention.
 *
 * @ORM\Table(name="hn_intervention_initiateur")
 * @ORM\Entity(repositoryClass="HopitalNumerique\InterventionBundle\Repository\InterventionInitiateurRepository")
 */
class InterventionInitiateur
{
    private static $INTERVENTION_INITIATEUR_CMSI = 1;
    private static $INTERVENTION_INITIATEUR_ETABLISSEMENT = 2;
    private static $INTERVENTION_INITIATEUR_ANAP = 3;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", name="intervinit_id", nullable=false, options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="intervinit_type", type="string", length=32, nullable=false)
     */
    private $type;

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
     * Set type
     *
     * @param string $type
     * @return InterventionInitiateur
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returne l'ID du CMSI.
     * 
     * @return integer ID du CMSI
     */
    public static function getInterventionInitiateurCmsiId()
    {
        return self::$INTERVENTION_INITIATEUR_CMSI;
    }
    /**
     * Returne l'ID de l'établissement.
     *
     * @return integer ID de l'établissement
     */
    public static function getInterventionInitiateurEtablissementId()
    {
        return self::$INTERVENTION_INITIATEUR_ETABLISSEMENT;
    }
    /**
     * Returne l'ID de l'ANAP.
     *
     * @return integer ID de l'ANAP
     */
    public static function getInterventionInitiateurAnapId()
    {
        return self::$INTERVENTION_INITIATEUR_ANAP;
    }

    /**
     * toString.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->type;
    }
}
