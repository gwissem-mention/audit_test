<?php
namespace HopitalNumerique\CommunautePratiqueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité Fiche.
 * 
 * @ORM\Entity()
 * @ORM\Table(name="hn_communautepratique_fiche")
 */
class Fiche
{
    /**
     * @var integer
     *
     * @ORM\Column(name="fic_id", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe
     *
     * @ORM\ManyToOne(targetEntity="Groupe", inversedBy="fiches")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="group_id", nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $groupe;

    /**
     * @var string
     *
     * @ORM\Column(name="fic_question_posee", type="string", nullable=false, length=255)
     * @Assert\NotBlank()
     */
    private $questionPosee;

    /**
     * @var string
     *
     * @ORM\Column(name="fic_contexte", type="text", nullable=false, options={"comment"="Éléments de contexte à prendre en compte"})
     * @Assert\NotBlank()
     */
    private $contexte;

    /**
     * @var string
     *
     * @ORM\Column(name="fic_description", type="text", nullable=false)
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="fic_aide_attendue", type="text", nullable=false)
     * @Assert\NotBlank()
     */
    private $aideAttendue;

    /**
     * @var string
     *
     * @ORM\Column(name="fic_resume", type="text", nullable=false)
     * @Assert\NotBlank()
     */
    private $resume;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Document", inversedBy="fiches")
     * @ORM\JoinTable(name="hn_communautepratique_fiche_document", joinColumns={@ORM\JoinColumn(name="fic_id", referencedColumnName="fic_id")}, inverseJoinColumns={@ORM\JoinColumn(name="doc_id", referencedColumnName="doc_id")})
     */
    private $documents;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->documents = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set questionPosee
     *
     * @param string $questionPosee
     * @return Fiche
     */
    public function setQuestionPosee($questionPosee)
    {
        $this->questionPosee = $questionPosee;

        return $this;
    }

    /**
     * Get questionPosee
     *
     * @return string 
     */
    public function getQuestionPosee()
    {
        return $this->questionPosee;
    }

    /**
     * Set contexte
     *
     * @param string $contexte
     * @return Fiche
     */
    public function setContexte($contexte)
    {
        $this->contexte = $contexte;

        return $this;
    }

    /**
     * Get contexte
     *
     * @return string 
     */
    public function getContexte()
    {
        return $this->contexte;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Fiche
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set aideAttendue
     *
     * @param string $aideAttendue
     * @return Fiche
     */
    public function setAideAttendue($aideAttendue)
    {
        $this->aideAttendue = $aideAttendue;

        return $this;
    }

    /**
     * Get aideAttendue
     *
     * @return string 
     */
    public function getAideAttendue()
    {
        return $this->aideAttendue;
    }

    /**
     * Set resume
     *
     * @param string $resume
     * @return Fiche
     */
    public function setResume($resume)
    {
        $this->resume = $resume;

        return $this;
    }

    /**
     * Get resume
     *
     * @return string 
     */
    public function getResume()
    {
        return $this->resume;
    }

    /**
     * Set groupe
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe
     * @return Fiche
     */
    public function setGroupe(\HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe $groupe)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return \HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Add documents
     *
     * @param \HopitalNumerique\CommunautePratiqueBundle\Entity\Document $documents
     * @return Fiche
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
}
