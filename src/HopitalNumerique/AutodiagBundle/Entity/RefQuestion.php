<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefQuestion
 *
 * @ORM\Table("hn_outil_question_reference")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\RefQuestionRepository")
 */
class RefQuestion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="refq_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", onDelete="CASCADE")
     */
    private $reference;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="references")
     * @ORM\JoinColumn(name="que_id", referencedColumnName="que_id", onDelete="CASCADE")
     */
    private $question;

    /**
     * @var boolean
     *
     * @ORM\Column(name="refq_primary", type="boolean", options = {"comment" = "La référence est de type primaire ?"})
     */
    private $primary;

    public function __construct()
    {
        $this->primary = true;
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
     * Get reference
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $reference
     */
    public function getReference()
    {
        return $this->reference;
    }
    
    /**
     * Set reference
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference
     */
    public function setReference(\HopitalNumerique\ReferenceBundle\Entity\Reference $reference)
    {
        $this->reference = $reference;
    }

    /**
     * Get question
     *
     * @return \HopitalNumerique\AutodiagBundle\Entity\Question $question
     */
    public function getQuestion()
    {
        return $this->question;
    }
    
    /**
     * Set question
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Question $question
     */
    public function setQuestion(\HopitalNumerique\AutodiagBundle\Entity\Question $question)
    {
        $this->question = $question;
        return $this;
    }

    /**
     * Get primary
     *
     * @return boolean $primary
     */
    public function getPrimary()
    {
        return $this->primary;
    }
    
    /**
     * Set primary
     *
     * @param boolean $primary
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;
        return $this;
    }   
}