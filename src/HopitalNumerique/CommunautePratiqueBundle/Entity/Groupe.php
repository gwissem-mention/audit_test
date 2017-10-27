<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire;
use Nodevo\RoleBundle\Entity\Role;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use HopitalNumerique\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entité Groupe.
 *
 * @ORM\Entity(repositoryClass="HopitalNumerique\CommunautePratiqueBundle\Repository\GroupeRepository")
 * @ORM\Table(name="hn_communautepratique_groupe")
 */
class Groupe
{
    /**
     * @var int
     *
     * @ORM\Column(name="group_id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="group_titre", type="string", nullable=false, length=255)
     * @Assert\NotBlank()
     */
    private $titre;

    /**
     * @var string
     *
     * @ORM\Column(name="group_description_courte", type="text", nullable=false)
     * @Assert\NotBlank()
     */
    private $descriptionCourte;

    /**
     * @var string
     *
     * @ORM\Column(name="group_description_html", type="text", nullable=false)
     * @Assert\NotBlank()
     */
    private $descriptionHtml;

    /**
     * @var int
     *
     * @ORM\Column(name="group_nombre_participants_maximum", type="smallint", nullable=false, options={"unsigned":true})
     * @Assert\NotNull()
     * @Assert\Range(
     *      min = 1
     * )
     */
    private $nombreParticipantsMaximum;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="group_date_inscription_ouverture", type="date", nullable=false)
     * @Assert\NotNull()
     */
    private $dateInscriptionOuverture;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="group_date_demarrage", type="date", nullable=false)
     * @Assert\NotNull()
     */
    private $dateDemarrage;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="group_date_fin", type="date", nullable=false)
     * @Assert\NotNull()
     */
    private $dateFin;

    /**
     * @var bool
     *
     * @ORM\Column(name="group_vedette", type="boolean", nullable=false, options={"default"=false})
     * @Assert\NotNull()
     */
    private $vedette;

    /**
     * @var bool
     *
     * @ORM\Column(name="group_actif", type="boolean", nullable=false, options={"default"=false})
     * @Assert\NotNull()
     */
    private $actif;

    /**
     * @var Domaine[]
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\DomaineBundle\Entity\Domaine", inversedBy="communautePratiqueGroupes")
     * @ORM\JoinTable(
     *     name="hn_communautepratique_groupe_domain",
     *     joinColumns={ @ORM\JoinColumn(name="group_id", referencedColumnName="group_id", onDelete="CASCADE")},
     *     inverseJoinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     * @Assert\NotNull()
     */
    protected $domains;

    /**
     * @var Questionnaire
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire", inversedBy="communautePratiqueGroupes")
     * @ORM\JoinColumn(name="qst_id", referencedColumnName="qst_id", nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $questionnaire;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="group_date_creation", type="datetime", nullable=false)
     */
    private $dateCreation;

    /**
     * @ORM\OneToMany(targetEntity="Fiche", mappedBy="groupe")
     */
    private $fiches;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\UserBundle\Entity\User", inversedBy="communautePratiqueAnimateurGroupes")
     * @ORM\JoinTable(name="hn_communautepratique_groupe_animateur", joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="group_id")}, inverseJoinColumns={@ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id")})
     * @Assert\Count(min=1, minMessage="Veuillez choisir au moins un animateur")
     */
    private $animateurs;

    /**
     * @var Collection
     */
    private $users;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Inscription", mappedBy="groupe", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $inscriptions;

    /**
     * @ORM\OneToMany(targetEntity="Document", mappedBy="groupe")
     */
    private $documents;

    /**
     * @ORM\OneToMany(targetEntity="HopitalNumerique\ObjetBundle\Entity\Objet", mappedBy="communautePratiqueGroupe")
     */
    private $publications;

    /**
     * @ORM\OneToMany(targetEntity="Commentaire", mappedBy="groupe")
     * @ORM\OrderBy({"dateCreation":"DESC"})
     */
    private $commentaires;

    /**
     * @var Role[]
     *
     * @ORM\ManyToMany(targetEntity="\Nodevo\RoleBundle\Entity\Role")
     * @ORM\JoinTable(name="hn_communautepratique_groupe_role",
     *      joinColumns={ @ORM\JoinColumn(name="group_id", referencedColumnName="group_id")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="ro_id", referencedColumnName="ro_id")}
     * )
     */
    protected $requiredRoles;

    /**
     * @var Discussion|null
     *
     * @ORM\OneToOne(targetEntity="HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion")
     */
    protected $presentationDiscussion;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->fiches = new ArrayCollection();
        $this->animateurs = new ArrayCollection();
        $this->inscriptions = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->publications = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->requiredRoles = new ArrayCollection();
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
     * @return Groupe
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
     * Set descriptionCourte.
     *
     * @param string $descriptionCourte
     *
     * @return Groupe
     */
    public function setDescriptionCourte($descriptionCourte)
    {
        $this->descriptionCourte = $descriptionCourte;

        return $this;
    }

    /**
     * Get descriptionCourte.
     *
     * @return string
     */
    public function getDescriptionCourte()
    {
        return $this->descriptionCourte;
    }

    /**
     * Set descriptionHtml.
     *
     * @param string $descriptionHtml
     *
     * @return Groupe
     */
    public function setDescriptionHtml($descriptionHtml)
    {
        $this->descriptionHtml = $descriptionHtml;

        return $this;
    }

    /**
     * Get descriptionHtml.
     *
     * @return string
     */
    public function getDescriptionHtml()
    {
        return $this->descriptionHtml;
    }

    /**
     * Set nombreParticipantsMaximum.
     *
     * @param int $nombreParticipantsMaximum
     *
     * @return Groupe
     */
    public function setNombreParticipantsMaximum($nombreParticipantsMaximum)
    {
        $this->nombreParticipantsMaximum = $nombreParticipantsMaximum;

        return $this;
    }

    /**
     * Get nombreParticipantsMaximum.
     *
     * @return int
     */
    public function getNombreParticipantsMaximum()
    {
        return $this->nombreParticipantsMaximum;
    }

    /**
     * Set dateInscriptionOuverture.
     *
     * @param \DateTime $dateInscriptionOuverture
     *
     * @return Groupe
     */
    public function setDateInscriptionOuverture($dateInscriptionOuverture)
    {
        $this->dateInscriptionOuverture = $dateInscriptionOuverture;

        return $this;
    }

    /**
     * Get dateInscriptionOuverture.
     *
     * @return \DateTime
     */
    public function getDateInscriptionOuverture()
    {
        return $this->dateInscriptionOuverture;
    }

    /**
     * Set dateDemarrage.
     *
     * @param \DateTime $dateDemarrage
     *
     * @return Groupe
     */
    public function setDateDemarrage($dateDemarrage)
    {
        $this->dateDemarrage = $dateDemarrage;

        return $this;
    }

    /**
     * Get dateDemarrage.
     *
     * @return \DateTime
     */
    public function getDateDemarrage()
    {
        return $this->dateDemarrage;
    }

    /**
     * Set dateFin.
     *
     * @param \DateTime $dateFin
     *
     * @return Groupe
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
     * Set vedette.
     *
     * @param bool $vedette
     *
     * @return Groupe
     */
    public function setVedette($vedette)
    {
        $this->vedette = $vedette;

        return $this;
    }

    /**
     * Get vedette.
     *
     * @return bool
     */
    public function getVedette()
    {
        return $this->vedette;
    }

    /**
     * Set actif.
     *
     * @param bool $actif
     *
     * @return Groupe
     */
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * Get actif.
     *
     * @return bool
     */
    public function getActif()
    {
        return $this->actif;
    }

    /**
     * Add domain
     *
     * @param Domaine $domain
     *
     * @return Groupe
     */
    public function addDomain(Domaine $domain)
    {
        if (!$this->domains->contains($domain)) {
            $this->domains->add($domain);
        }

        return $this;
    }

    /**
     * Get domaine.
     *
     * @return Domaine[]
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * Set questionnaire.
     *
     * @param Questionnaire $questionnaire
     *
     * @return Groupe
     */
    public function setQuestionnaire(Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;

        return $this;
    }

    /**
     * Get questionnaire.
     *
     * @return Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * Set dateCreation.
     *
     * @param \DateTime $dateCreation
     *
     * @return Groupe
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation.
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Add fiches.
     *
     * @param Fiche $fiches
     *
     * @return Groupe
     */
    public function addFiche(Fiche $fiches)
    {
        $this->fiches[] = $fiches;

        return $this;
    }

    /**
     * Remove fiches.
     *
     * @param Fiche $fiches
     */
    public function removeFiche(Fiche $fiches)
    {
        $this->fiches->removeElement($fiches);
    }

    /**
     * Get fiches.
     *
     * @return Collection
     */
    public function getFiches()
    {
        return $this->fiches;
    }

    /**
     * Add animateur.
     *
     * @param User $animateur
     *
     * @return Groupe
     */
    public function addAnimateur(User $animateur)
    {
        $this->animateurs[] = $animateur;

        if (!$this->hasUser($animateur) && !$this->isRegister($animateur)) {
            $this->addUser($animateur);
        }

        return $this;
    }

    /**
     * Remove animateurs.
     *
     * @param User $animateurs
     */
    public function removeAnimateur(User $animateurs)
    {
        $this->animateurs->removeElement($animateurs);
    }

    /**
     * Get animateurs.
     *
     * @return Collection
     */
    public function getAnimateurs()
    {
        return $this->animateurs;
    }

    /**
     * Add users.
     *
     * @param User $users
     *
     * @return Groupe
     */
    public function addUser(User $users)
    {
        $this->addInscription(new Inscription($this, $users));

        return $this;
    }

    /**
     * Remove users.
     *
     * @param User $user
     */
    public function removeUser(User $user)
    {
        foreach ($this->getInscriptions() as $inscription) {
            if ($user->equals($inscription->getUser())) {
                $this->removeInscription($inscription);
                break;
            }
        }
    }

    /**
     * Get users.
     *
     * @return Collection
     */
    public function getUsers()
    {
        $inscrits = new ArrayCollection();
        foreach ($this->getInscriptions() as $inscrit) {
            $inscrits[] = $inscrit->getUser();
        }

        return $inscrits;
    }

    /**
     * Add inscription.
     *
     * @param Inscription $inscription
     *
     * @return Groupe
     */
    public function addInscription(Inscription $inscription)
    {
        if ($this->inscriptions->filter(function (Inscription $registration) use ($inscription) {
            return
                $registration->getUser()->getId() === $inscription->getUser()->getId() &&
                $registration->getGroupe()->getId() === $inscription->getGroupe()->getId()
            ;
        })->count() === 0) {
            $this->inscriptions[] = $inscription;
        }

        return $this;
    }

    /**
     * Remove inscription.
     *
     * @param Inscription $inscription
     */
    public function removeInscription(Inscription $inscription)
    {
        $this->inscriptions->removeElement($inscription);
    }

    /**
     * Get inscriptions.
     *
     * @return Collection
     */
    public function getInscriptions()
    {
        return $this->inscriptions;
    }

    /**
     * @return Inscription[]|Collection
     */
    public function getValidatedInscriptions()
    {
        return $this->getInscriptions()->filter(function (Inscription $registration) {
            return $registration->isActif();
        });
    }

    /**
     * @return Inscription[]|Collection
     */
    public function getInscriptionsToValidate()
    {
        return $this->getInscriptions()->filter(function (Inscription $registration) {
            return !$registration->isActif();
        });
    }

    /**
     * Check if user is register in the group.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isRegister(User $user)
    {
        foreach($this->inscriptions as $register) {
            if($register->getUser()->getId() === $user->getId())
                return true;
        }

        return false;
    }

    /**
     * Add documents.
     *
     * @param Document $documents
     *
     * @return Groupe
     */
    public function addDocument(Document $documents)
    {
        $this->documents[] = $documents;

        return $this;
    }

    /**
     * Remove documents.
     *
     * @param Document $documents
     */
    public function removeDocument(Document $documents)
    {
        $this->documents->removeElement($documents);
    }

    /**
     * Get documents.
     *
     * @return Collection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Add publications.
     *
     * @param Objet $publications
     *
     * @return Groupe
     */
    public function addPublication(Objet $publications)
    {
        $this->publications[] = $publications;

        return $this;
    }

    /**
     * Remove publications.
     *
     * @param Objet $publications
     */
    public function removePublication(Objet $publications)
    {
        $this->publications->removeElement($publications);
    }

    /**
     * Get publications.
     *
     * @return Collection
     */
    public function getPublications()
    {
        return $this->publications;
    }

    /**
     * Add commentaires.
     *
     * @param Commentaire $commentaires
     *
     * @return Groupe
     */
    public function addCommentaire(Commentaire $commentaires)
    {
        $this->commentaires[] = $commentaires;

        return $this;
    }

    /**
     * Remove commentaires.
     *
     * @param Commentaire $commentaires
     */
    public function removeCommentaire(Commentaire $commentaires)
    {
        $this->commentaires->removeElement($commentaires);
    }

    /**
     * Get commentaires.
     *
     * @return Collection
     */
    public function getCommentaires()
    {
        return $this->commentaires;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->titre;
    }

    /**
     * Retourne si le groupe est entre la date de démarrage et la date de fin.
     *
     * @return bool VRAI si en cours
     */
    public function isEnCours()
    {
        $aujourdhui = new \DateTime();
        $aujourdhui->setTime(0, 0, 0);

        return $aujourdhui >= $this->dateDemarrage && $aujourdhui <= $this->dateFin;
    }

    /**
     * Retourne si le groupe est entre la date de démarrage et la date de fin.
     *
     * @return bool VRAI si en cours
     */
    public function isPeriodeInscription()
    {
        $aujourdhui = new \DateTime();
        $aujourdhui->setTime(0, 0, 0);

        return $aujourdhui >= $this->dateInscriptionOuverture && $aujourdhui <= $this->dateDemarrage;
    }

    /**
     * Retourne si l'utilisateur est animateur du groupe.
     *
     * @param User $user Utilisateur
     *
     * @return bool VRAI si animateur
     */
    public function hasAnimateur(User $user)
    {
        foreach ($this->animateurs as $animateur) {
            if ($animateur->getId() == $user->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne les utilisateurs qui ne sont pas animateurs.
     *
     * @return array $user
     */
    public function getUsersWithoutAnimateurs()
    {
        if (is_null($this->users)) {
            return [];
        }

        $collection = [];
        foreach ($this->users as $user) {
            if (!$this->hasAnimateur($user)) {
                $collection[$user->getId()] = $user;
            }
        }

        return $collection;
    }

    /**
     * Retourne si l'utilisateur est membre du groupe.
     *
     * @param User $user Utilisateur
     *
     * @return bool VRAI si membre
     */
    public function hasUser(User $user)
    {
        if ($this->users != null) {
            foreach ($this->users as $membre) {
                if ($membre->getId() == $user->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Retourne le nombre de places restantes.
     *
     * @return int Nombre de places restantes
     */
    public function getNombrePlacesRestantes()
    {
        $nombrePlacesRestantes = $this->nombreParticipantsMaximum - count($this->users);

        return $nombrePlacesRestantes > 0 ? $nombrePlacesRestantes : 0;
    }

    /**
     * Retourne le nombre de jours qu'il reste avant l'ouverture des inscriptions.
     *
     * @return int Nombre de jours
     */
    public function getNombreJoursRestantsAvantInscriptionOuverture()
    {
        $aujourdhui = new \DateTime();
        $aujourdhui->setTime(0, 0, 0);

        $dateInterval = $aujourdhui->diff($this->dateInscriptionOuverture);

        return intval($dateInterval->format('%R%a'));
    }

    /**
     * Retourne la date de dernière activité du groupe.
     *
     * @return \DateTime Dernière activité
     */
    public function getDateDerniereActivite()
    {
        $dateDerniereActivite = $this->dateCreation;

        foreach ($this->commentaires as $commentaire) {
            if (null === $dateDerniereActivite || $commentaire->getDateCreation() > $dateDerniereActivite) {
                $dateDerniereActivite = $commentaire->getDateCreation();
            }
        }

        foreach ($this->fiches as $fiche) {
            if (null === $dateDerniereActivite || $fiche->getDateCreation() > $dateDerniereActivite) {
                $dateDerniereActivite = $fiche->getDateCreation();
            }
            if (null !== $fiche->getDateDerniereActivite()
                    && $fiche->getDateDerniereActivite() > $dateDerniereActivite) {
                $dateDerniereActivite = $fiche->getDateDerniereActivite();
            }
        }

        return $dateDerniereActivite;
    }

    /**
     * Retourne le total de tous les commentaires, fiches comprises.
     *
     * @return int Total des commentaires
     */
    public function getTotalCommentaires()
    {
        $totalCommentaires = count($this->getCommentaires());

        foreach ($this->fiches as $fiche) {
            $totalCommentaires += count($fiche->getCommentaires());
        }

        return $totalCommentaires;
    }

    /**
     * Retourne le total de tous les commentaires d'un utilisateur, fiches comprises.
     *
     * @param User $user Utilisateur
     *
     * @return int Total des commentaires
     */
    public function getTotalUserCommentaires(User $user)
    {
        $totalCommentaires = 0;

        foreach ($this->getCommentaires() as $commentaire) {
            if ($commentaire->getUser()->getId() == $user->getId()) {
                ++$totalCommentaires;
            }
        }

        foreach ($this->fiches as $fiche) {
            foreach ($fiche->getCommentaires() as $commentaire) {
                if ($commentaire->getUser()->getId() == $user->getId()) {
                    ++$totalCommentaires;
                }
            }
        }

        return $totalCommentaires;
    }

    /**
     * Retourne les fiches d'un utilisateur.
     *
     * @param User $user Utilisateur
     *
     * @return Fiche[] Fiches de l'utilisateur
     */
    public function getUserFiches(User $user)
    {
        $userFiches = [];

        foreach ($this->fiches as $fiche) {
            if ($fiche->getUser()->getId() == $user->getId()) {
                $userFiches[] = $fiche;
            }
        }

        return $userFiches;
    }

    /**
     * Retourne les documents d'un utilisateur.
     *
     * @param User $user Utilisateur
     *
     * @return Document[] Documents de l'utilisateur
     */
    public function getUserDocuments(User $user)
    {
        $userDocuments = [];

        foreach ($this->documents as $document) {
            if ($document->getUser()->getId() == $user->getId()) {
                $userDocuments[] = $document;
            }
        }

        return $userDocuments;
    }

    /**
     * Retourne le JSON avec toutes les adresses électroniques des animateurs.
     *
     * @return string JSON
     */
    public function getAnimateurEmailsJson()
    {
        $animateurs = [];

        /** @var User $animateur */
        foreach ($this->animateurs as $animateur) {
            $animateurs[$animateur->getEmail()] = trim($animateur->getFirstname() . ' ' . $animateur->getLastname());
        }

        return str_replace('"', '\'', json_encode($animateurs));
    }

    /**
     * @return Role[]|ArrayCollection
     */
    public function getRequiredRoles()
    {
        return $this->requiredRoles;
    }

    /**
     * @param Role $role
     *
     * @return Groupe
     */
    public function addRequiredRole(Role $role)
    {
        if (!$this->requiredRoles->contains($role)) {
            $this->requiredRoles->add($role);
        }

        return $this;
    }

    /**
     * @return Discussion|null
     */
    public function getPresentationDiscussion()
    {
        return $this->presentationDiscussion;
    }

    /**
     * @param Discussion|null $presentationDiscussion
     *
     * @return Groupe
     */
    public function setPresentationDiscussion(Discussion $presentationDiscussion = null)
    {
        $this->presentationDiscussion = $presentationDiscussion;

        return $this;
    }
}
