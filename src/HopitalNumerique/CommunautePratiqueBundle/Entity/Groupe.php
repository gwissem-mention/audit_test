<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\UserBundle\Entity\User", inversedBy="communautePratiqueAnimateurGroupes")
     * @ORM\JoinTable(name="hn_communautepratique_groupe_animateur", joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="group_id")}, inverseJoinColumns={@ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id")})
     */
    private $animateurs;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\UserBundle\Entity\User", inversedBy="communautePratiqueGroupes")
     * @ORM\JoinTable(name="hn_communautepratique_groupe_user", joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="group_id")}, inverseJoinColumns={@ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id")})
     */
    private $users;


    /**
     * Constructor
     */
    public function __construct()
    {
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
     * Add animateurs
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $animateurs
     * @return Groupe
     */
    public function addAnimateur(\HopitalNumerique\UserBundle\Entity\User $animateurs)
    {
        $this->animateurs[] = $animateurs;

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
     * @return string
     */
    public function __toString()
    {
        return $this->titre;
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

        return json_encode($animateurs);
    }
}
