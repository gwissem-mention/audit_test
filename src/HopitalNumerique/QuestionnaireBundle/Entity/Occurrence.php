<?php
namespace HopitalNumerique\QuestionnaireBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Occurrence.
 *
 * @ORM\Table("hn_questionnaire_occurrence")
 * @ORM\Entity()
 */
class Occurrence
{
    /**
     * @var integer
     *
     * @ORM\Column(name="occ_id", type="integer", options={"unsigned"="true"})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="occ_libelle", type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $libelle;
    
    /**
     * @var \HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire
     * 
     * @ORM\ManyToOne(targetEntity="Questionnaire", inversedBy="occurrences")
     * @ORM\JoinColumn(name="qst_id", referencedColumnName="qst_id", nullable=false, onDelete="CASCADE")
     */
    private $questionnaire;
    
    /**
     * @var \HopitalNumerique\UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\UserBundle\Entity\User", inversedBy="questionnaireOccurrences")
     * @ORM\JoinColumn(name="usr_id", referencedColumnName="usr_id", nullable=false, onDelete="CASCADE")
     */
    private $user;
    
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="Reponse", mappedBy="occurence")
     */
    private $reponses;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reponses = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set libelle
     *
     * @param string $libelle
     * @return Occurrence
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
     * Set questionnaire
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Questionnaire $questionnaire
     * @return Occurrence
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
     * Set user
     *
     * @param \HopitalNumerique\UserBundle\Entity\User $user
     * @return Occurrence
     */
    public function setUser(\HopitalNumerique\UserBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \HopitalNumerique\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add reponses
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Reponse $reponses
     * @return Occurrence
     */
    public function addReponse(\HopitalNumerique\QuestionnaireBundle\Entity\Reponse $reponses)
    {
        $this->reponses[] = $reponses;

        return $this;
    }

    /**
     * Remove reponses
     *
     * @param \HopitalNumerique\QuestionnaireBundle\Entity\Reponse $reponses
     */
    public function removeReponse(\HopitalNumerique\QuestionnaireBundle\Entity\Reponse $reponses)
    {
        $this->reponses->removeElement($reponses);
    }

    /**
     * Get reponses
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReponses()
    {
        return $this->reponses;
    }
    
    
    /**
     * @return string
     */
    public function __toString()
    {
        return ($this->libelle ?: 'Sans titre');
    }
}
