<?php

namespace HopitalNumerique\ExpertBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use HopitalNumerique\UserBundle\Entity\User;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * ActiviteExpert.
 *
 * @ORM\Table(name="hn_expert_activite")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ExpertBundle\Repository\ActiviteExpertRepository")
 */
class ActiviteExpert
{
    /**
     * @var int
     *
     * @ORM\Column(name="exp_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Liste des événements liés à l'activité.
     *
     * @var /HopitalNumerique/ExpertBundle/Entity/EvenementExpert
     *
     * @ORM\OneToMany(targetEntity="EvenementExpert", mappedBy="activite", cascade={"persist", "remove" })
     * @ORM\OrderBy({"date" = "DESC"})
     */
    protected $evenements;

    /**
     * Liste des dates fictives liées à l'activité.
     *
     * @var /HopitalNumerique/ExpertBundle/Entity/DateFictiveActiviteExpert
     *
     * @ORM\OneToMany(targetEntity="DateFictiveActiviteExpert", mappedBy="activite", cascade={"remove" })
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
     * @var int
     *
     * @ORM\Column(name="exp_nb_vacation_par_expert", type="integer")
     */
    protected $nbVacationParExpert;

    /**
     * @var bool
     *
     * @ORM\Column(name="exp_etat_validation", type="boolean")
     */
    protected $etatValidation;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_type_activite", referencedColumnName="ref_id")
     *
     * @GRID\Column(field="activities.libelle", options = {"comment" = "Type d activite sur la table référence avec le code ACTIVITE_TYPE"})
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
     * @var array</HopitalNumerique/ExpertBundle/Entity/ActiviteExpert/Paiement>
     *
     * @ORM\OneToMany(targetEntity="HopitalNumerique\ExpertBundle\Entity\ActiviteExpert\Paiement", mappedBy="activiteExpert", cascade={"persist"}, orphanRemoval=true)
     */
    private $paiements;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->expertConcernes = new ArrayCollection();
        $this->anapiens = new ArrayCollection();
        $this->etatValidation = false;
        $this->paiements = new ArrayCollection();
    }

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
     * Set titre.
     *
     * @param string $titre
     *
     * @return ActiviteExpert
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre.
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set dateDebut.
     *
     * @param \DateTime $dateDebut
     *
     * @return ActiviteExpert
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get dateDebut.
     *
     * @return \DateTime
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set dateFin.
     *
     * @param \DateTime $dateFin
     *
     * @return ActiviteExpert
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get dateFin.
     *
     * @return \DateTime
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set nbVacationParExpert.
     *
     * @param int $nbVacationParExpert
     *
     * @return ActiviteExpert
     */
    public function setNbVacationParExpert($nbVacationParExpert)
    {
        $this->nbVacationParExpert = $nbVacationParExpert;

        return $this;
    }

    /**
     * Get nbVacationParExpert.
     *
     * @return int
     */
    public function getNbVacationParExpert()
    {
        return $this->nbVacationParExpert;
    }

    /**
     * Set etatValidation.
     *
     * @param bool $etatValidation
     *
     * @return ActiviteExpert
     */
    public function setEtatValidation($etatValidation)
    {
        $this->etatValidation = $etatValidation;

        return $this;
    }

    /**
     * Get etatValidation.
     *
     * @return bool
     */
    public function getEtatValidation()
    {
        return $this->etatValidation;
    }

    /**
     * Set activities.
     *
     * @param Reference $typeActivite
     *
     * @return ActiviteExpert
     */
    public function setTypeActivite(Reference $typeActivite = null)
    {
        $this->typeActivite = $typeActivite;

        return $this;
    }

    /**
     * Get activities.
     *
     * @return Reference
     */
    public function getTypeActivite()
    {
        return $this->typeActivite;
    }

    /**
     * Add expertConcernes.
     *
     * @param User $expertConcernes
     *
     * @return ActiviteExpert
     */
    public function addExpertConcerne(User $expertConcernes)
    {
        $this->expertConcernes[] = $expertConcernes;

        return $this;
    }

    /**
     * Remove expertConcernes.
     *
     * @param User $expertConcernes
     */
    public function removeExpertConcerne(User $expertConcernes)
    {
        $this->expertConcernes->removeElement($expertConcernes);
    }

    /**
     * Get expertConcernes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExpertConcernes()
    {
        return $this->expertConcernes;
    }

    /**
     * Set uniteOeuvreConcerne.
     *
     * @param Reference $uniteOeuvreConcerne
     *
     * @return ActiviteExpert
     */
    public function setUniteOeuvreConcerne(Reference $uniteOeuvreConcerne = null)
    {
        $this->uniteOeuvreConcerne = $uniteOeuvreConcerne;

        return $this;
    }

    /**
     * Get uniteOeuvreConcerne.
     *
     * @return Reference
     */
    public function getUniteOeuvreConcerne()
    {
        return $this->uniteOeuvreConcerne;
    }

    /**
     * Add anapiens.
     *
     * @param User $anapiens
     *
     * @return ActiviteExpert
     */
    public function addAnapien(User $anapiens)
    {
        $this->anapiens[] = $anapiens;

        return $this;
    }

    /**
     * Remove anapiens.
     *
     * @param User $anapiens
     */
    public function removeAnapien(User $anapiens)
    {
        $this->anapiens->removeElement($anapiens);
    }

    /**
     * Get anapiens.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAnapiens()
    {
        return $this->anapiens;
    }

    /**
     * Set prestataire.
     *
     * @param Reference $prestataire
     *
     * @return ActiviteExpert
     */
    public function setPrestataire(Reference $prestataire = null)
    {
        $this->prestataire = $prestataire;

        return $this;
    }

    /**
     * Get prestataire.
     *
     * @return Reference
     */
    public function getPrestataire()
    {
        return $this->prestataire;
    }

    /**
     * Set etat.
     *
     * @param Reference $etat
     *
     * @return ActiviteExpert
     */
    public function setEtat(Reference $etat = null)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat.
     *
     * @return Reference
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * Add evenements.
     *
     * @param EvenementExpert $evenements
     *
     * @return ActiviteExpert
     */
    public function addEvenement(EvenementExpert $evenements)
    {
        $this->evenements[] = $evenements;

        return $this;
    }

    /**
     * Remove evenements.
     *
     * @param EvenementExpert $evenements
     */
    public function removeEvenement(EvenementExpert $evenements)
    {
        $this->evenements->removeElement($evenements);
    }

    /**
     * Get evenements.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvenements()
    {
        return $this->evenements;
    }

    /**
     * Get evenements.
     *
     * @return int
     */
    public function getMiniNbPresenceEvenements()
    {
        $nbMiniPresence = -1;

        $presenceExpertByActivite = [];
        /** @var EvenementExpert $evenement */
        foreach ($this->evenements as $evenement) {
            foreach ($evenement->getExperts() as $expertPresence) {
                if ($expertPresence->getPresent()) {
                    if (!array_key_exists($expertPresence->getExpertConcerne()->getId(), $presenceExpertByActivite)) {
                        $presenceExpertByActivite[$expertPresence->getExpertConcerne()->getId()] = 0;
                    }

                    $presenceExpertByActivite[$expertPresence->getExpertConcerne()->getId()]
                        += $evenement->getNbVacation()
                    ;
                }
            }
        }

        foreach ($presenceExpertByActivite as $nbVacationParExpert) {
            if ($nbVacationParExpert < $nbMiniPresence || $nbMiniPresence === -1) {
                $nbMiniPresence = $nbVacationParExpert;
            }
        }

        return $nbMiniPresence;
    }

    /**
     * Add dateFictives.
     *
     * @param EvenementExpert $dateFictives
     *
     * @return ActiviteExpert
     */
    public function addDateFictive(EvenementExpert $dateFictives)
    {
        $this->dateFictives[] = $dateFictives;

        return $this;
    }

    /**
     * Remove dateFictives.
     *
     * @param EvenementExpert $dateFictives
     */
    public function removeDateFictive(EvenementExpert $dateFictives)
    {
        $this->dateFictives->removeElement($dateFictives);
    }

    /**
     * Get dateFictives.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDateFictives()
    {
        return $this->dateFictives;
    }

    /**
     * Add paiement.
     *
     * @param ActiviteExpert\Paiement $paiement
     *
     * @return ActiviteExpert
     */
    public function addPaiement(ActiviteExpert\Paiement $paiement)
    {
        $this->paiements[] = $paiement;

        return $this;
    }

    /**
     * Remove paiement.
     *
     * @param ActiviteExpert\Paiement $paiement
     */
    public function removePaiement(ActiviteExpert\Paiement $paiement)
    {
        $this->paiements->removeElement($paiement);
    }

    /**
     * Get paiements.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPaiements()
    {
        return $this->paiements;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->titre;
    }

    /**
     * Retourne si l'expert est présent.
     *
     * @param User $expert Expert
     *
     * @return bool Vrai présent
     */
    public function hasExpertConcerne(User $expert)
    {
        foreach ($this->expertConcernes as $expertConcerne) {
            if ($expertConcerne->getId() === $expert->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne si le paiement d'un expert existe.
     *
     * @param User $expert Expert
     *
     * @return bool Vrai s'il existe
     */
    public function hasPaiementForExpert(User $expert)
    {
        foreach ($this->paiements as $paiement) {
            if ($paiement->getExpert()->getId() === $expert->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Supprime le paiement d'un expert.
     *
     * @param User $expert Expert
     */
    public function removePaiementForExpert(User $expert)
    {
        foreach ($this->paiements as $paiement) {
            if ($paiement->getExpert()->getId() === $expert->getId()) {
                $this->removePaiement($paiement);
                break;
            }
        }
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return null === $this->id;
    }
}
