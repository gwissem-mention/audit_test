<?php

namespace HopitalNumerique\ExpertBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * EvenementExpert
 *
 * @ORM\Table(name="hn_expert_evenement")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ExpertBundle\Repository\EvenementExpertRepository")
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
     * Liste des experts lié à l'activité pour gerer leur présence
     * 
     * @var /HopitalNumerique/ExpertBundle/Entity/EvenementPresenceExpert
     * 
     * @ORM\OneToMany(targetEntity="EvenementPresenceExpert", mappedBy="evenement", cascade={"persist", "remove" })
     */
    protected $experts;

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

    /**
     * Add experts
     *
     * @param \HopitalNumerique\ExpertBundle\Entity\EvenementPresenceExpert $experts
     * @return EvenementExpert
     */
    public function addExpert(\HopitalNumerique\ExpertBundle\Entity\EvenementPresenceExpert $experts)
    {
        $this->experts[] = $experts;

        return $this;
    }

    /**
     * Remove experts
     *
     * @param \HopitalNumerique\ExpertBundle\Entity\EvenementPresenceExpert $experts
     */
    public function removeExpert(\HopitalNumerique\ExpertBundle\Entity\EvenementPresenceExpert $experts)
    {
        $this->experts->removeElement($experts);
    }

    /**
     * Get experts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getExperts()
    {
        return $this->experts;
    }

    /**
     * Get nombre de présents dans les experts
     *
     * @return integer 
     */
    public function getExpertsPresents()
    {
        $nbPresents = 0;

        foreach ($this->experts as $expert) 
        {
            if(!is_null($expert->getExpertConcerne()) && $expert->getPresent())
            {
                $nbPresents += $this->nbVacation;
            }
        }

        return $nbPresents;
    }

    /**
     * Get les ids des domaines concerné par l'user
     *
     * @return array[integer]
     */
    public function getExpertsIds()
    {
        $expertsId = array();

        if(is_null($this->experts))
        {
            return array();
        }

        foreach ($this->experts as $expert) 
        {
            if(!is_null($expert->getExpertConcerne()))
            {
                $expertsId[] = $expert->getExpertConcerne()->getId();
            }
        }

        return $expertsId;
    }
}
