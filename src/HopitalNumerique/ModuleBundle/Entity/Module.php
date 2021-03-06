<?php

namespace HopitalNumerique\ModuleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * Module.
 *
 * @ORM\Table(name="hn_module")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ModuleBundle\Repository\ModuleRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class Module
{
    /**
     * @var int
     *
     * @ORM\Column(name="mod_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Liste des sessions liées au module.
     *
     * @var /HopitalNumerique/ModuleBundle/Entity/Session
     *
     * @ORM\OneToMany(targetEntity="Session", mappedBy="module", cascade={"persist", "remove" })
     * @ORM\OrderBy({"dateSession" = "ASC"})
     */
    protected $sessions;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="mod_date_derniere_maj", type="datetime")
     */
    protected $dateLastUpdate;

    /**
     * @var string
     * @Assert\NotBlank(message="Le titre ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le titre.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le titre."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     * @ORM\Column(name="mod_titre", type="string", length=255, options = {"comment" = "Titre du module"})
     */
    protected $titre;

    /**
     * @var int
     *
     * @Assert\NotBlank(message="Les productions ne peuvent pas être vides.")
     * @Nodevo\Javascript(class="validate[required]")
     *
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ObjetBundle\Entity\Objet", inversedBy="modules")
     * @ORM\JoinTable(name="hn_module_objet",
     *      joinColumns={ @ORM\JoinColumn(name="mod_id", referencedColumnName="mod_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="obj_id", referencedColumnName="obj_id")}
     * )
     * @ORM\OrderBy({"titre" = "ASC"})
     *
     * @GRID\Column(field="productions.titre")
     */
    protected $productions;

    /**
     * @var int
     *
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinTable(name="hn_module_connaissances",
     *      joinColumns={ @ORM\JoinColumn(name="mod_id", referencedColumnName="mod_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id")}
     * )
     * @ORM\OrderBy({"order" = "ASC"})
     *
     * @GRID\Column(field="connaissances.libelle")
     */
    protected $connaissances;

    /**
     * @var int
     *
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinTable(name="hn_module_connaissances_metier",
     *      joinColumns={ @ORM\JoinColumn(name="mod_id", referencedColumnName="mod_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id")}
     * )
     * @ORM\OrderBy({"order" = "ASC"})
     *
     * @GRID\Column(field="connaissancesMetier.libelle")
     */
    protected $connaissancesMetier;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_duree", referencedColumnName="ref_id")
     *
     * @GRID\Column(field="duree.libelle", nullable=true, options = {"comment" = "Durée pointant sur la table référence avec le code DUREE_FORMATION du module"})
     */
    protected $duree;

    /**
     * @var string
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans les horaires types.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans les horaires types."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[255]]")
     * @ORM\Column(name="mod_horairesType", type="string", length=255, nullable=true, options = {"comment" = "Horaires type du module"})
     */
    protected $horairesType;

    /**
     * @var string
     *
     * @ORM\Column(name="mod_lieu", type="text", nullable=true, options = {"comment" = "Lieu du module"})
     */
    protected $lieu;

    /**
     * @var string
     *
     * @ORM\Column(name="mod_description", type="text", nullable=true, options = {"comment" = "Description du module"})
     */
    protected $description;

    /**
     * @var int
     *
     * @Nodevo\Javascript(class="validate[custom[integer],min[0]]")
     * @ORM\Column(name="mod_nombrePlaceDisponible", type="integer", nullable=true, options = {"comment" = "Nombre de places disponibles du module"})
     */
    protected $nombrePlaceDisponible;

    /**
     * @var string
     *
     * @ORM\Column(name="mod_prerequis", type="text", nullable=true, options = {"comment" = "Prérequis du module"})
     */
    protected $prerequis;

    /**
     * @Assert\File(
     *     maxSize = "10M",
     *     mimeTypes = {
     *         "application/pdf",
     *         "application/x-pdf",
     *         "application/vnd.ms-excel",
     *         "application/msword",
     *         "application/xls",
     *         "application/x-xls",
     *         "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
     *         "application/vnd.ms-powerpoint",
     *         "application/vnd.openxmlformats-officedocument.presentationml.presentation",
     *         "image/gif",
     *         "image/jpeg",
     *         "image/png",
     *         "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
     *         "application/zip",
     *         "application/vnd.oasis.opendocument.text ",
     *         "application/vnd.oasis.opendocument.graphics",
     *         "application/vnd.oasis.opendocument.presentation",
     *         "application/vnd.oasis.opendocument.spreadsheet",
     *         "application/vnd.oasis.opendocument.chart",
     *         "application/vnd.oasis.opendocument.formula",
     *         "application/vnd.oasis.opendocument.database",
     *         "application/vnd.oasis.opendocument.image",
     *         "application/vnd.openofficeorg.extension"
     *     },
     *     mimeTypesMessage = "Choisissez un fichier valide (PDF, EXCEL, WORD, POWER POINT, ZIP, IMAGE)"
     * )
     */
    public $file;

    /**
     * @var string
     *
     * @ORM\Column(name="mod_piecejointe", type="string", length=255, nullable=true, options = {"comment" = "Nom du fichier stocké"})
     */
    protected $path;

    /**
     * @var string
     *
     * @ORM\Column(name="mod_mail", type="text", nullable=true, options = {"comment" = "Texte dans le mail de rappel"})
     */
    protected $textMailRappel;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="usr_formateur", referencedColumnName="usr_id", nullable=true, onDelete="SET NULL")
     *
     * @GRID\Column(field="formateur.lastname")
     */
    protected $formateur;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_statut", referencedColumnName="ref_id")
     * @Nodevo\Javascript(class="validate[required]")
     *
     * @GRID\Column(field="statut.libelle", options = {"comment" = "Statut pointant sur la table reference avec le code ETAT du module"})
     */
    protected $statut;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine", cascade={"persist"})
     * @ORM\JoinTable(name="hn_domaine_gestions_modules",
     *      joinColumns={ @ORM\JoinColumn(name="mod_id", referencedColumnName="mod_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     */
    protected $domaines;

    /**
     * @var bool
     *
     * @ORM\Column(name="mod_accuse_inscription", type="boolean")
     */
    protected $mailAccuseInscription;

    /**
     * @var bool
     *
     * @ORM\Column(name="mod_confirmation_inscription", type="boolean")
     */
    protected $mailConfirmationInscription;

    /**
     * @var bool
     *
     * @ORM\Column(name="mod_refus_inscription", type="boolean")
     */
    protected $mailRefusInscription;

    /**
     * @var bool
     *
     * @ORM\Column(name="mod_rappel_evaluation", type="boolean")
     */
    protected $mailRappelEvalution;

    /**
     * @var bool
     *
     * @ORM\Column(name="mod_alerte_evaluation", type="boolean")
     */
    protected $mailAlerteEvaluation;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->sessions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->productions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->connaissances = new \Doctrine\Common\Collections\ArrayCollection();
        $this->connaissancesMetier = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get dateLastUpdate.
     *
     * @return DateTime $dateLastUpdate
     */
    public function getDateLastUpdate()
    {
        return $this->dateLastUpdate;
    }

    /**
     * Set dateLastUpdate.
     *
     * @param DateTime $dateLastUpdate
     */
    public function setDateLastUpdate($dateLastUpdate)
    {
        $this->dateLastUpdate = $dateLastUpdate;

        return $this;
    }

    /**
     * Set titre.
     *
     * @param string $titre
     *
     * @return Module
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
     * Add production.
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet $production
     *
     * @return Objet
     */
    public function addProduction(\HopitalNumerique\ObjetBundle\Entity\Objet $production)
    {
        $this->productions[] = $production;

        return $this;
    }

    /**
     * Remove production.
     *
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet $production
     */
    public function removeProduction(\HopitalNumerique\ObjetBundle\Entity\Objet $production)
    {
        $this->productions->removeElement($production);
    }

    /**
     * Set productions.
     *
     * @param \Doctrine\Common\Collections\Collection $productions
     *
     * @return Objet
     */
    public function setProductions(array $productions)
    {
        $this->productions = $productions;

        return $this;
    }

    /**
     * Get productions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductions()
    {
        return $this->productions;
    }

    /**
     * Set horairesType.
     *
     * @param string $horairesType
     *
     * @return Module
     */
    public function setHorairesType($horairesType)
    {
        $this->horairesType = $horairesType;

        return $this;
    }

    /**
     * Get horairesType.
     *
     * @return string
     */
    public function getHorairesType()
    {
        return $this->horairesType;
    }

    /**
     * Set lieu.
     *
     * @param string $lieu
     *
     * @return Module
     */
    public function setLieu($lieu)
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * Get lieu.
     *
     * @return string
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * Set textMailRappel.
     *
     * @param string $textMailRappel
     *
     * @return Module
     */
    public function setTextMailRappel($textMailRappel)
    {
        $this->textMailRappel = $textMailRappel;

        return $this;
    }

    /**
     * Get textMailRappel.
     *
     * @return string
     */
    public function getTextMailRappel()
    {
        return $this->textMailRappel;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Module
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set nombrePlaceDisponible.
     *
     * @param int $nombrePlaceDisponible
     *
     * @return Module
     */
    public function setNombrePlaceDisponible($nombrePlaceDisponible)
    {
        $this->nombrePlaceDisponible = $nombrePlaceDisponible;

        return $this;
    }

    /**
     * Get nombrePlaceDisponible.
     *
     * @return int
     */
    public function getNombrePlaceDisponible()
    {
        return $this->nombrePlaceDisponible;
    }

    /**
     * Set prerequis.
     *
     * @param string $prerequis
     *
     * @return Module
     */
    public function setPrerequis($prerequis)
    {
        $this->prerequis = $prerequis;

        return $this;
    }

    /**
     * Get prerequis.
     *
     * @return string
     */
    public function getPrerequis()
    {
        return $this->prerequis;
    }

    /**
     * Set formateur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $formateur
     *
     * @return Reponse
     */
    public function setFormateur(\HopitalNumerique\UserBundle\Entity\User $formateur = null)
    {
        $this->formateur = $formateur;

        return $this;
    }

    /**
     * Get formateur.
     *
     * @return \HopitalNumerique\UserBundle\Entity\User
     */
    public function getFormateur()
    {
        return $this->formateur;
    }

    /**
     * Get statut.
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $statut
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set statut.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $statut
     */
    public function setStatut($statut)
    {
        if ($statut instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference) {
            $this->statut = $statut;
        } else {
            $this->statut = null;
        }
    }

    /**
     * Get duree.
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $duree
     */
    public function getDuree()
    {
        return $this->duree;
    }

    /**
     * Set duree.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $duree
     */
    public function setDuree($duree)
    {
        if ($duree instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference) {
            $this->duree = $duree;
        } else {
            $this->duree = null;
        }
    }

    /**
     * Add sessions.
     *
     * @param \HopitalNumerique\ModuleBundle\Entity\Session $sessions
     *
     * @return \HopitalNumerique\ModuleBundle\Entity\Module
     */
    public function addSession(\HopitalNumerique\ModuleBundle\Entity\Session $sessions)
    {
        $this->sessions[] = $sessions;

        return $this;
    }

    /**
     * Remove sessions.
     *
     * @param \HopitalNumerique\ModuleBundle\Entity\Session $sessions
     */
    public function removeSession(\HopitalNumerique\ModuleBundle\Entity\Session $sessions)
    {
        $this->sessions->removeElement($sessions);
    }

    /**
     * Get sessions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSessions()
    {
        return $this->sessions;
    }

    /**
     * Get sessions actives.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSessionsActives()
    {
        $sessions = [];

        foreach ($this->sessions as $session) {
            if (SessionStatus::STATUT_SESSION_FORMATION_ACTIVE_ID === $session->getEtat()->getId()) {
                $sessions[] = $session;
            }
        }

        return $sessions;
    }

    /**
     * Get sessions dont la date d'inscription est en ce moment.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSessionsInscriptionsOuvertes()
    {
        $sessions = [];

        foreach ($this->sessions as $session) {
            if ($session->getDateOuvertureInscription() <= new \DateTime()
                && $session->getDateFermetureInscription() >= new \DateTime()
                && $session->getEtat()->getId() === SessionStatus::STATUT_SESSION_FORMATION_ACTIVE_ID
                && !$session->getArchiver()) {
                $sessions[] = $session;
            }
        }

        return $sessions;
    }

    // ----------------------------------------
    // --- Gestion de l'upload des fichiers ---
    // ----------------------------------------

    /**
     * Set path.
     *
     * @param string $path
     *
     * @return Contractualisation
     */
    public function setPath($path)
    {
        if (is_null($path) && file_exists($this->getAbsolutePath())) {
            unlink($this->getAbsolutePath());
        }

        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir() . '/' . $this->path;
    }

    public function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __ROOT_DIRECTORY__ . '/' . $this->getUploadDir();
    }

    public function getUploadDir()
    {
        return 'files/modules';
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file) {
            //delete Old File
            if (file_exists($this->getAbsolutePath())) {
                unlink($this->getAbsolutePath());
            }

            $this->path = round(microtime(true) * 1000) . '_' . $this->file->getClientOriginalName();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        // s'il y a une erreur lors du déplacement du fichier, une exception
        // va automatiquement être lancée par la méthode move(). Cela va empêcher
        // proprement l'entité d'être persistée dans la base de données si
        // erreur il y a
        $this->file->move($this->getUploadRootDir(), $this->path);

        unset($this->file);
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        $file = $this->getAbsolutePath();

        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * toString.
     *
     * @return string Titre
     */
    public function __toString()
    {
        return $this->titre;
    }

    /**
     * Add connaissances.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $connaissances
     *
     * @return Module
     */
    public function addConnaissance(\HopitalNumerique\ReferenceBundle\Entity\Reference $connaissances)
    {
        $this->connaissances[] = $connaissances;

        return $this;
    }

    /**
     * Remove connaissances.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $connaissances
     */
    public function removeConnaissance(\HopitalNumerique\ReferenceBundle\Entity\Reference $connaissances)
    {
        $this->connaissances->removeElement($connaissances);
    }

    /**
     * Get connaissances.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConnaissances()
    {
        return $this->connaissances;
    }

    public function getConnaissancesByParent()
    {
        $connaissances = $this->connaissances;
        $connaissancesOrdered = [];

        foreach ($connaissances as $connaissance) {
            if (!array_key_exists($connaissance->getParent()->getId(), $connaissancesOrdered)) {
                $connaissancesOrdered[$connaissance->getParent()->getId()] = [];
            }

            $connaissancesOrdered[$connaissance->getParent()->getId()][] = $connaissance;
        }

        return $connaissancesOrdered;
    }

    /**
     * Add domaines.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     *
     * @return Module
     */
    public function addDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines[] = $domaines;

        return $this;
    }

    /**
     * Remove domaines.
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     */
    public function removeDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines->removeElement($domaines);
    }

    /**
     * Get domaines.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDomaines()
    {
        return $this->domaines;
    }

    /**
     * Get les ids des domaines.
     *
     * @return array[integer]
     */
    public function getDomainesId()
    {
        $domainesId = [];

        foreach ($this->domaines as $domaine) {
            $domainesId[] = $domaine->getId();
        }

        return $domainesId;
    }

    /**
     * Set mailAccuseInscription.
     *
     * @param bool $mailAccuseInscription
     *
     * @return Module
     */
    public function setMailAccuseInscription($mailAccuseInscription)
    {
        $this->mailAccuseInscription = $mailAccuseInscription;

        return $this;
    }

    /**
     * Get mailAccuseInscription.
     *
     * @return bool
     */
    public function getMailAccuseInscription()
    {
        return $this->mailAccuseInscription;
    }

    /**
     * Set mailConfirmationInscription.
     *
     * @param bool $mailConfirmationInscription
     *
     * @return Module
     */
    public function setMailConfirmationInscription($mailConfirmationInscription)
    {
        $this->mailConfirmationInscription = $mailConfirmationInscription;

        return $this;
    }

    /**
     * Get mailConfirmationInscription.
     *
     * @return bool
     */
    public function getMailConfirmationInscription()
    {
        return $this->mailConfirmationInscription;
    }

    /**
     * Set mailRefusInscription.
     *
     * @param bool $mailRefusInscription
     *
     * @return Module
     */
    public function setMailRefusInscription($mailRefusInscription)
    {
        $this->mailRefusInscription = $mailRefusInscription;

        return $this;
    }

    /**
     * Get mailRefusInscription.
     *
     * @return bool
     */
    public function getMailRefusInscription()
    {
        return $this->mailRefusInscription;
    }

    /**
     * Set mailRappelEvalution.
     *
     * @param bool $mailRappelEvalution
     *
     * @return Module
     */
    public function setMailRappelEvalution($mailRappelEvalution)
    {
        $this->mailRappelEvalution = $mailRappelEvalution;

        return $this;
    }

    /**
     * Get mailRappelEvalution.
     *
     * @return bool
     */
    public function getMailRappelEvalution()
    {
        return $this->mailRappelEvalution;
    }

    /**
     * Set mailAlerteEvaluation.
     *
     * @param bool $mailAlerteEvaluation
     *
     * @return Module
     */
    public function setMailAlerteEvaluation($mailAlerteEvaluation)
    {
        $this->mailAlerteEvaluation = $mailAlerteEvaluation;

        return $this;
    }

    /**
     * Get mailAlerteEvaluation.
     *
     * @return bool
     */
    public function getMailAlerteEvaluation()
    {
        return $this->mailAlerteEvaluation;
    }

    // Connaissances Métiers

    /**
     * Add connaissancesMetier.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $connaissancesMetier
     *
     * @return Session
     */
    public function addConnaissanceMetier(\HopitalNumerique\ReferenceBundle\Entity\Reference $connaissancesMetier)
    {
        $this->connaissancesMetier[] = $connaissancesMetier;

        return $this;
    }

    /**
     * Remove connaissancesMetier.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $connaissancesMetier
     */
    public function removeConnaissanceMetier(\HopitalNumerique\ReferenceBundle\Entity\Reference $connaissancesMetier)
    {
        $this->connaissancesMetier->removeElement($connaissancesMetier);
    }

    /**
     * Get connaissancesMetier.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConnaissancesMetier()
    {
        return $this->connaissancesMetier;
    }

    /**
     * Set connaissancesMetier.
     *
     * @return Session
     */
    public function setConnaissancesMetier($connaissancesMetier)
    {
        $this->connaissancesMetier = $connaissancesMetier;

        return $this;
    }

    public function getConnaissancesMetierByParent()
    {
        $connaissancesMetier = $this->connaissancesMetier;
        $connaissancesMetierOrdered = [];

        foreach ($connaissancesMetier as $connaissanceMetier) {
            if (!array_key_exists($connaissanceMetier->getParent()->getId(), $connaissancesMetierOrdered)) {
                $connaissancesMetierOrdered[$connaissanceMetier->getParent()->getId()] = [];
            }

            $connaissancesMetierOrdered[$connaissanceMetier->getParent()->getId()][] = $connaissanceMetier;
        }

        return $connaissancesMetierOrdered;
    }
}
