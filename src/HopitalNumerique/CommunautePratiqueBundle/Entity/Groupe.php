<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use HopitalNumerique\UserBundle\Entity\User;

/**
 * Entité Groupe.
 *
 * @ORM\Entity(repositoryClass="HopitalNumerique\CommunautePratiqueBundle\Repository\GroupeRepository")
 * @ORM\Table(name="hn_communautepratique_groupe")
 */
class Groupe
{
    /**
     * @var integer
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
     * @var integer
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
     * @var boolean
     *
     * @ORM\Column(name="group_vedette", type="boolean", nullable=false, options={"default"=false})
     * @Assert\NotNull()
     */
    private $vedette;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="group_actif", type="boolean", nullable=false, options={"default"=false})
     * @Assert\NotNull()
     */
    private $actif;
    
    /**
     * @var \HopitalNumerique\DomaineBundle\Entity\Domaine
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\DomaineBundle\Entity\Domaine", inversedBy="communautePratiqueGroupes")
     * @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $domaine;

    /**
     * @var \HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire", inversedBy="communautePratiqueGroupes")
     * @ORM\JoinColumn(name="qst_id", referencedColumnName="qst_id", nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $questionnaire;

    /**
     * @ORM\OneToMany(targetEntity="Fiche", mappedBy="groupe")
     */
    private $fiches;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\UserBundle\Entity\User", inversedBy="communautePratiqueAnimateurGroupes")
     * @ORM\JoinTable(name="hn_communautepratique_groupe_animateur", joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="group_id")}, inverseJoinColumns={@ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id")})
     */
    private $animateurs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\UserBundle\Entity\User", inversedBy="communautePratiqueGroupes", cascade={"persist"})
     * @ORM\JoinTable(name="hn_communautepratique_groupe_user", joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="group_id")}, inverseJoinColumns={@ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id")})
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="Document", mappedBy="groupe")
     */
    private $documents;

    /**
     * @ORM\OneToMany(targetEntity="Commentaire", mappedBy="groupe")
     */
    private $commentaires;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fiches = new \Doctrine\Common\Collections\ArrayCollection();
        $this->animateurs = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Groupe
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
     * Set descriptionCourte
     *
     * @param string $descriptionCourte
     * @return Groupe
     */
    public function setDescriptionCourte($descriptionCourte)
    {
        $this->descriptionCourte = $descriptionCourte;

        return $this;
    }

    /**
     * Get descriptionCourte
     *
     * @return string 
     */
    public function getDescriptionCourte()
    {
        return $this->descriptionCourte;
    }

    /**
     * Set descriptionHtml
     *
     * @param string $descriptionHtml
     * @return Groupe
     */
    public function setDescriptionHtml($descriptionHtml)
    {
        $this->descriptionHtml = $descriptionHtml;

        return $this;
    }

    /**
     * Get descriptionHtml
     *
     * @return string 
     */
    public function getDescriptionHtml()
    {
        return $this->descriptionHtml;
    }

    /**
     * Set nombreParticipantsMaximum
     *
     * @param integer $nombreParticipantsMaximum
     * @return Groupe
     */
    public function setNombreParticipantsMaximum($nombreParticipantsMaximum)
    {
        $this->nombreParticipantsMaximum = $nombreParticipantsMaximum;

        return $this;
    }

    /**
     * Get nombreParticipantsMaximum
     *
     * @return integer 
     */
    public function getNombreParticipantsMaximum()
    {
        return $this->nombreParticipantsMaximum;
    }

    /**
     * Set dateInscriptionOuverture
     *
     * @param \DateTime $dateInscriptionOuverture
     * @return Groupe
     */
    public function setDateInscriptionOuverture($dateInscriptionOuverture)
    {
        $this->dateInscriptionOuverture = $dateInscriptionOuverture;

        return $this;
    }

    /**
     * Get dateInscriptionOuverture
     *
     * @return \DateTime 
     */
    public function getDateInscriptionOuverture()
    {
        return $this->dateInscriptionOuverture;
    }

    /**
     * Set dateDemarrage
     *
     * @param \DateTime $dateDemarrage
     * @return Groupe
     */
    public function setDateDemarrage($dateDemarrage)
    {
        $this->dateDemarrage = $dateDemarrage;

        return $this;
    }

    /**
     * Get dateDemarrage
     *
     * @return \DateTime 
     */
    public function getDateDemarrage()
    {
        return $this->dateDemarrage;
    }

    /**
     * Set dateFin
     *
     * @param \DateTime $dateFin
     * @return Groupe
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
     * Set vedette
     *
     * @param boolean $vedette
     * @return Groupe
     */
    public function setVedette($vedette)
    {
        $this->vedette = $vedette;

        return $this;
    }

    /**
     * Get vedette
     *
     * @return boolean 
     */
    public function getVedette()
    {
        return $this->vedette;
    }

    /**
     * Set actif
     *
     * @param boolean $actif
     * @return Groupe
     */
    public function setActif($actif)
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * Get actif
     *
     * @return boolean 
     */
    public function getActif()
    {
        return $this->actif;
    }

    /**
     * Set domaine
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaine
     * @return Groupe
     */
    public function setDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaine)
    {
        $this->domaine = $domaine;

        return $this;
    }

    /**
     * Get domaine
     *
     * @return \HopitalNumerique\DomaineBundle\Entity\Domaine 
     */
    public function getDomaine()
    {
        return $this->domaine;
    }

    /**
     * Set questionnaire
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire $questionnaire
     * @return Groupe
     */
    public function setQuestionnaire(\HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;

        return $this;
    }

    /**
     * Get questionnaire
     *
     * @return \HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire 
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * Add fiches
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche $fiches
     * @return Groupe
     */
    public function addFiche(\HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche $fiches)
    {
        $this->fiches[] = $fiches;

        return $this;
    }

    /**
     * Remove fiches
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche $fiches
     */
    public function removeFiche(\HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche $fiches)
    {
        $this->fiches->removeElement($fiches);
    }

    /**
     * Get fiches
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFiches()
    {
        return $this->fiches;
    }

    /**
     * Add animateur
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $animateur
     * @return Groupe
     */
    public function addAnimateur(\HopitalNumerique\UserBundle\Entity\User $animateur)
    {
        $this->animateurs[] = $animateur;

        if (!$this->hasUser($animateur)) {
            $this->addUser($animateur);
        }

        return $this;
    }

    /**
     * Remove animateurs
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $animateurs
     */
    public function removeAnimateur(\HopitalNumerique\UserBundle\Entity\User $animateurs)
    {
        $this->animateurs->removeElement($animateurs);
    }

    /**
     * Get animateurs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAnimateurs()
    {
        return $this->animateurs;
    }

    /**
     * Add users
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $users
     * @return Groupe
     */
    public function addUser(\HopitalNumerique\UserBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $users
     */
    public function removeUser(\HopitalNumerique\UserBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add documents
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Document $documents
     * @return Groupe
     */
    public function addDocument(\HopitalNumerique\CommunautePratiqueBundle\Entity\Document $documents)
    {
        $this->documents[] = $documents;

        return $this;
    }

    /**
     * Remove documents
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Document $documents
     */
    public function removeDocument(\HopitalNumerique\CommunautePratiqueBundle\Entity\Document $documents)
    {
        $this->documents->removeElement($documents);
    }

    /**
     * Get documents
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Add commentaires
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire $commentaires
     * @return Groupe
     */
    public function addCommentaire(\HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire $commentaires)
    {
        $this->commentaires[] = $commentaires;

        return $this;
    }

    /**
     * Remove commentaires
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire $commentaires
     */
    public function removeCommentaire(\HopitalNumerique\CommunautePratiqueBundle\Entity\Commentaire $commentaires)
    {
        $this->commentaires->removeElement($commentaires);
    }

    /**
     * Get commentaires
     *
     * @return \Doctrine\Common\Collections\Collection 
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
     * Retourne si l'utilisateur est animateur du groupe.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user Utilisateur
     * @return boolean VRAI si animateur
     */
    public function hasAnimateur(\HopitalNumerique\UserBundle\Entity\User $user)
    {
        foreach ($this->animateurs as $animateur) {
            if ($animateur->getId() == $user->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne si l'utilisateur est membre du groupe.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user Utilisateur
     * @return boolean VRAI si membre
     */
    public function hasUser(\HopitalNumerique\UserBundle\Entity\User $user)
    {
        foreach ($this->users as $membre) {
            if ($membre->getId() == $user->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne le nombre de places restantes.
     * 
     * @return integer Nombre de places restantes
     */
    public function getNombrePlacesRestantes()
    {
        $nombrePlacesRestantes = $this->nombreParticipantsMaximum - count($this->users);
        
        return ($nombrePlacesRestantes > 0 ? $nombrePlacesRestantes : 0);
    }

    /**
     * Retourne le nombre de jours qu'il reste avant l'ouverture des inscriptions.
     * 
     * @return integer Nombre de jours
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
     * @return \DateTime|NULL Dernière activité
     */
    public function getDateDerniereActivite()
    {
        $dateDerniereActivite = null;

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
     * @return integer Total des commentaires
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
     * @param \HopitalNumerique\UserBundle\Entity\User $user Utilisateur
     * @return integer Total des commentaires
     */
    public function getTotalUserCommentaires(User $user)
    {
        $totalCommentaires = 0;

        foreach ($this->getCommentaires() as $commentaire) {
            if ($commentaire->getUser()->getId() == $user->getId()) {
                $totalCommentaires++;
            }
        }

        foreach ($this->fiches as $fiche) {
            foreach ($fiche->getCommentaires() as $commentaire) {
                if ($commentaire->getUser()->getId() == $user->getId()) {
                    $totalCommentaires++;
                }
            }
        }

        return $totalCommentaires;
    }

    /**
     * Retourne les fiches d'un utilisateur.
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user Utilisateur
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Fiche> Fiches de l'utilisateur
     */
    public function getUserFiches(User $user)
    {
        $userFiches = array();

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
     * @param \HopitalNumerique\UserBundle\Entity\User $user Utilisateur
     * @return array<\HopitalNumerique\CommunautePratiqueBundle\Entity\Document> Documents de l'utilisateur
     */
    public function getUserDocuments(User $user)
    {
        $userDocuments = array();

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
        $animateurs = array();

        foreach ($this->animateurs as $animateur)
        {
            $animateurs[$animateur->getEmail()] = trim($animateur->getPrenom().' '.$animateur->getNom());
        }

        return str_replace( '"', '\'', json_encode($animateurs) );
    }
}
