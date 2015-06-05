<?php

namespace HopitalNumerique\ExpertBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * EvenementExpert
 *
 * @ORM\Table(name="hn_expert_evenement")
 * @ORM\Entity
 */
class EvenementExpert
{
    /**
     * @var integer
     *
     * @ORM\Column(name="eveE_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="ActiviteExpert", inversedBy="evenements")
     * @ORM\JoinColumn(name="eveE_activite", referencedColumnName="exp_id")
     */
    protected $activite;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_nom", referencedColumnName="ref_id")
     * 
     * @GRID\Column(field="nom.libelle")
     */
    protected $nom;

    /**
     * @var integer
     *
     * @ORM\Column(name="eveE_nb_vacation", type="integer")
     */
    protected $nbVacation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="eveE_date", type="datetime")
     * 
     * @GRID\Column(type="datetime", format="d/m/Y")
     */
    protected $date;

    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        $this->nbVacation = 1;
    }


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
     * Set date
     *
     * @param \DateTime $date
     * @return EvenementExpert
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set nbVacation
     *
     * @param integer $nbVacation
     * @return EvenementExpert
     */
    public function setNbVacation($nbVacation)
    {
        $this->nbVacation = $nbVacation;

        return $this;
    }

    /**
     * Get nbVacation
     *
     * @return integer 
     */
    public function getNbVacation()
    {
        return $this->nbVacation;
    }

    /**
     * Set nom
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $nom
     * @return EvenementExpert
     */
    public function setNom(\HopitalNumerique\ReferenceBundle\Entity\Reference $nom = null)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set activite
     *
     * @param \HopitalNumerique\ExpertBundle\Entity\ActiviteExpert $activite
     * @return EvenementExpert
     */
    public function setActivite(\HopitalNumerique\ExpertBundle\Entity\ActiviteExpert $activite = null)
    {
        $this->activite = $activite;

        return $this;
    }

    /**
     * Get activite
     *
     * @return \HopitalNumerique\ExpertBundle\Entity\ActiviteExpert 
     */
    public function getActivite()
    {
        return $this->activite;
    }
}
