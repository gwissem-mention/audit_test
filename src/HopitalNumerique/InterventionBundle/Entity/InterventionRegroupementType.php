<?php

namespace HopitalNumerique\InterventionBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\InterventionBundle\Entity\InterventionRegroupementType;

/**
 * Entité d'un type de regroupement d'intervention.
 *
 * @ORM\Table(name="hn_intervention_regroupement_type")
 * @ORM\Entity
 */
class InterventionRegroupementType
{
    /**
     * @var integer ID du type de regroupement d'intervention Objet similaire
     */
    private static $INTERVENTION_REGROUPEMENT_TYPE_OBJET_ID = 1;
    /**
     * @var integer ID du type de regroupement d'intervention Ambassadeur
     */
    private static $INTERVENTION_REGROUPEMENT_TYPE_AMBASSADEUR_ID = 2;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", name="intervregtyp_id", nullable=false, options={"unsigned":true})
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

    
    /**
     * Retourne l'ID du type de regroupement d'intervention Objet similaire.
     *
     * @return integer ID du type de regroupement d'intervention Objet similaire
     */
    public static function getInterventionRegroupementTypeObjetId()
    {
        return self::$INTERVENTION_REGROUPEMENT_TYPE_OBJET_ID;
    }
    /**
     * Retourne l'ID du type de regroupement d'intervention Ambassadeur.
     *
     * @return integer ID du type de regroupement d'intervention Ambassadeur
     */
    public static function getInterventionRegroupementTypeAmbassadeurId()
    {
        return self::$INTERVENTION_REGROUPEMENT_TYPE_AMBASSADEUR_ID;
    }
}
