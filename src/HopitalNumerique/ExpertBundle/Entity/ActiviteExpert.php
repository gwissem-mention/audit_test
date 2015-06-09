<?php

namespace HopitalNumerique\ExpertBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * ActiviteExpert
 *
 * @ORM\Table(name="hn_expert_activite")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ExpertBundle\Repository\ActiviteExpertRepository")
 */
class ActiviteExpert
{
    /**
     * @var integer
     *
     * @ORM\Column(name="exp_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Liste des événements liés à l'activité
     * 
     * @var /HopitalNumerique/ExpertBundle/Entity/EvenementExpert
     * 
     * @ORM\OneToMany(targetEntity="EvenementExpert", mappedBy="activite", cascade={"persist", "remove" })
     * @ORM\OrderBy({"date" = "DESC"})
     */
    protected $evenements;

    /**
     * Liste des dates fictives liées à l'activité
     * 
     * @var /HopitalNumerique/ExpertBundle/Entity/EvenementExpert
     * 
     * @ORM\OneToMany(targetEntity="EvenementExpert", mappedBy="activite", cascade={"persist", "remove" })
     * @ORM\OrderBy({"date" = "DESC"})
     */
    protected $dateFictives;

    /**
     * @var string
     *
     * @ORM\Column(name="exp_titre", type="string", length=255)
     */
    protected $titre;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="exp_date_debut", type="datetime")
     */
    protected $dateDebut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="exp_date_fin", type="datetime")
     */
    protected $dateFin;

    /**
     * @var integer
     *
     * @ORM\Column(name="exp_nb_vacation_par_expert", type="integer")
     */
    protected $nbVacationParExpert;

    /**
     * @var boolean
     *
     * @ORM\Column(name="exp_etat_validation", type="boolean")
     */
    protected $etatValidation;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_type_activite", referencedColumnName="ref_id")
     *
     * @GRID\Column(field="typeActivite.libelle", options = {"comment" = "Type d activite sur la table référence avec le code ACTIVITE_TYPE"})
     */
    protected $typeActivite;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinTable(name="hn_expert_activite_experts",
     *      joinColumns={ @ORM\JoinColumn(name="exp_id", referencedColumnName="exp_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")}
     * )
     */
    protected $expertConcernes;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_unite_oeuvre_concerne", referencedColumnName="ref_id")
     *
     * @GRID\Column(field="uniteOeuvreConcerne.libelle", options = {"comment" = "Type d activite sur la table référence avec le code UO_PRESTATAIRE"})
     */
    protected $uniteOeuvreConcerne;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinTable(name="hn_expert_activite_anapiens",
     *      joinColumns={ @ORM\JoinColumn(name="exp_id", referencedColumnName="exp_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", onDelete="CASCADE")}
     * )
     */
    protected $anapiens;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_prestataire", referencedColumnName="ref_id")
     *
     * @GRID\Column(field="prestataire.libelle", options = {"comment" = "Type d activite sur la table référence avec le code PRESTATAIRE"})
     */
    protected $prestataire;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_etat", referencedColumnName="ref_id")
     *
     * @GRID\Column(field="etat.libelle", options = {"comment" = "Type d activite sur la table référence avec le code ACTIVITE_EXPERT_ETAT"})
     */
    protected $etat;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->expertConcernes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->anapiens = new \Doctrine\Common\Collections\ArrayCollection();
        $this->etatValidation = false;
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
     * Set titre
     *
     * @param string $titre
     * @return ActiviteExpert
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set dateDebut
     *
     * @param \DateTime $dateDebut
     * @return ActiviteExpert
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return \DateTime 
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin
     *
     * @param \DateTime $dateFin
     * @return ActiviteExpert
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get dateFin
     *
     * @return \DateTime 
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set nbVacationParExpert
     *
     * @param integer $nbVacationParExpert
     * @return ActiviteExpert
     */
    public function setNbVacationParExpert($nbVacationParExpert)
    {
        $this->nbVacationParExpert = $nbVacationParExpert;

        return $this;
    }

    /**
     * Get nbVacationParExpert
     *
     * @return integer 
     */
    public function getNbVacationParExpert()
    {
        return $this->nbVacationParExpert;
    }

    /**
     * Set etatValidation
     *
     * @param boolean $etatValidation
     * @return ActiviteExpert
     */
    public function setEtatValidation($etatValidation)
    {
        $this->etatValidation = $etatValidation;

        return $this;
    }

    /**
     * Get etatValidation
     *
     * @return boolean 
     */
    public function getEtatValidation()
    {
        return $this->etatValidation;
    }


    /**
     * Set typeActivite
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $typeActivite
     * @return ActiviteExpert
     */
    public function setTypeActivite(\HopitalNumerique\ReferenceBundle\Entity\Reference $typeActivite = null)
    {
        $this->typeActivite = $typeActivite;

        return $this;
    }

    /**
     * Get typeActivite
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getTypeActivite()
    {
        return $this->typeActivite;
    }

    /**
     * Add expertConcernes
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $expertConcernes
     * @return ActiviteExpert
     */
    public function addExpertConcerne(\HopitalNumerique\UserBundle\Entity\User $expertConcernes)
    {
        $this->expertConcernes[] = $expertConcernes;

        return $this;
    }

    /**
     * Remove expertConcernes
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $expertConcernes
     */
    public function removeExpertConcerne(\HopitalNumerique\UserBundle\Entity\User $expertConcernes)
    {
        $this->expertConcernes->removeElement($expertConcernes);
    }

    /**
     * Get expertConcernes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getExpertConcernes()
    {
        return $this->expertConcernes;
    }

    /**
     * Set uniteOeuvreConcerne
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $uniteOeuvreConcerne
     * @return ActiviteExpert
     */
    public function setUniteOeuvreConcerne(\HopitalNumerique\ReferenceBundle\Entity\Reference $uniteOeuvreConcerne = null)
    {
        $this->uniteOeuvreConcerne = $uniteOeuvreConcerne;

        return $this;
    }

    /**
     * Get uniteOeuvreConcerne
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getUniteOeuvreConcerne()
    {
        return $this->uniteOeuvreConcerne;
    }

    /**
     * Add anapiens
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $anapiens
     * @return ActiviteExpert
     */
    public function addAnapien(\HopitalNumerique\UserBundle\Entity\User $anapiens)
    {
        $this->anapiens[] = $anapiens;

        return $this;
    }

    /**
     * Remove anapiens
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $anapiens
     */
    public function removeAnapien(\HopitalNumerique\UserBundle\Entity\User $anapiens)
    {
        $this->anapiens->removeElement($anapiens);
    }

    /**
     * Get anapiens
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAnapiens()
    {
        return $this->anapiens;
    }

    /**
     * Set prestataire
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $prestataire
     * @return ActiviteExpert
     */
    public function setPrestataire(\HopitalNumerique\ReferenceBundle\Entity\Reference $prestataire = null)
    {
        $this->prestataire = $prestataire;

        return $this;
    }

    /**
     * Get prestataire
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getPrestataire()
    {
        return $this->prestataire;
    }

    /**
     * Set etat
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $etat
     * @return ActiviteExpert
     */
    public function setEtat(\HopitalNumerique\ReferenceBundle\Entity\Reference $etat = null)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference 
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * Add evenements
     *
     * @param \HopitalNumerique\ExpertBundle\Entity\EvenementExpert $evenements
     * @return ActiviteExpert
     */
    public function addEvenement(\HopitalNumerique\ExpertBundle\Entity\EvenementExpert $evenements)
    {
        $this->evenements[] = $evenements;

        return $this;
    }

    /**
     * Remove evenements
     *
     * @param \HopitalNumerique\ExpertBundle\Entity\EvenementExpert $evenements
     */
    public function removeEvenement(\HopitalNumerique\ExpertBundle\Entity\EvenementExpert $evenements)
    {
        $this->evenements->removeElement($evenements);
    }

    /**
     * Get evenements
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEvenements()
    {
        return $this->evenements;
    }

    /**
     * Get evenements
     *
     * @return integer
     */
    public function getMiniNbPresenceEvenements()
    {
        $nbMiniPresence = -1;

        foreach ($this->evenements as $evenement) 
        {
            if($nbMiniPresence === -1 || $nbMiniPresence > $evenement->getExpertsPresents())
            {
                $nbMiniPresence = $evenement->getExpertsPresents();
            }
        }

        return $nbMiniPresence;
    }

    /**
     * Add dateFictives
     *
     * @param \HopitalNumerique\ExpertBundle\Entity\EvenementExpert $dateFictives
     * @return ActiviteExpert
     */
    public function addDateFictive(\HopitalNumerique\ExpertBundle\Entity\EvenementExpert $dateFictives)
    {
        $this->dateFictives[] = $dateFictives;

        return $this;
    }

    /**
     * Remove dateFictives
     *
     * @param \HopitalNumerique\ExpertBundle\Entity\EvenementExpert $dateFictives
     */
    public function removeDateFictive(\HopitalNumerique\ExpertBundle\Entity\EvenementExpert $dateFictives)
    {
        $this->dateFictives->removeElement($dateFictives);
    }

    /**
     * Get dateFictives
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDateFictives()
    {
        return $this->dateFictives;
    }
}
