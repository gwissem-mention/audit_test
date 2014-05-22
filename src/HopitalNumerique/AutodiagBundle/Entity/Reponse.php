<?php

namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reponse
 *
 * @ORM\Table("hn_outil_reponse")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AutodiagBundle\Repository\ReponseRepository")
 */
class Reponse
{
    /**
     * @var integer
     *
     * @ORM\Column(name="rep_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="rep_value", type="string", length=255)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="rep_remarque", type="string", length=512)
     */
    private $remarque;

    /**
     * @ORM\ManyToOne(targetEntity="Resultat", cascade={"persist"})
     * @ORM\JoinColumn(name="res_id", referencedColumnName="res_id", onDelete="CASCADE")
     */
    protected $resultat;

    /**
     * @ORM\ManyToOne(targetEntity="Question", cascade={"persist"})
     * @ORM\JoinColumn(name="que_id", referencedColumnName="que_id", onDelete="CASCADE")
     */
    protected $question;

    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        
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
     * Set value
     *
     * @param string $value
     * @return Reponse
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get remarque
     *
     * @return string $remarque
     */
    public function getRemarque()
    {
        return $this->remarque;
    }
    
    /**
     * Set remarque
     *
     * @param string $remarque
     */
    public function setRemarque($remarque)
    {
        $this->remarque = $remarque;
        return $this;
    }

    /**
     * Get resultat
     *
     * @return \HopitalNumerique\AutodiagBundle\Entity\Resultat $resultat
     */
    public function getResultat()
    {
        return $this->resultat;
    }
    
    /**
     * Set resultat
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Resultat $resultat
     */
    public function setResultat(\HopitalNumerique\AutodiagBundle\Entity\Resultat $resultat)
    {
        $this->resultat = $resultat;
        return $this;
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
}
