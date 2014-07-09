<?php

namespace HopitalNumerique\RechercheBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExpBesoinReponses
 *
 * @ORM\Table(name="hn_recherche_expbesoin_reponse")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheBundle\Repository\ExpBesoinReponsesRepository")
 */
class ExpBesoinReponses
{
    /**
     * @var integer
     *
     * @ORM\Column(name="expbr_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="expb_order", type="smallint", options = {"comment" = "Ordre de la question"})
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(name="expbr_libelle", type="string", length=255)
     */
    protected $libelle;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\RechercheBundle\Entity\ExpBesoin")
     * @ORM\JoinColumn(name="expb_id", referencedColumnName="expb_id", nullable=true, onDelete="CASCADE")
     */
    protected $question;

    /**
     * @var boolean
     *
     * @ORM\Column(name="expbr_autreQuestion", type="boolean", options = {"comment" = " ?"})
     */
    protected $autreQuestion;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference")
     * @ORM\JoinColumn(name="ref_id", referencedColumnName="ref_id", nullable=true, onDelete="CASCADE")
     */
    protected $redirigeReference;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\RechercheBundle\Entity\ExpBesoin")
     * @ORM\JoinColumn(name="expb_id", referencedColumnName="expb_id", nullable=true, onDelete="CASCADE")
     */
    protected $redirigeQuestion;


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
     * Get order
     *
     * @return integer $order
     */
    public function getOrder()
    {
        return $this->order;
    }
    
    /**
     * Set order
     *
     * @param integer $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return ExpBesoin
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
     * Get question
     *
     * @return \HopitalNumerique\RechercheBundle\Entity\ExpBesoin $question
     */
    public function getQuestion()
    {
        return $this->question;
    }
    
    /**
     * Set question
     *
     * @param \HopitalNumerique\RechercheBundle\Entity\ExpBesoin $question
     */
    public function setQuestion(\HopitalNumerique\RechercheBundle\Entity\ExpBesoin $expBesoin)
    {
        $this->question = $expBesoin;
    }

    /**
     * Set autreQuestion
     *
     * @param boolean $autreQuestion
     * @return ExpBesoin
     */
    public function setAutreQuestion($autreQuestion)
    {
        $this->autreQuestion = $autreQuestion;

        return $this;
    }

    /**
     * Get autreQuestion
     *
     * @return boolean 
     */
    public function isAutreQuestion()
    {
        return $this->autreQuestion;
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
     * Get redirigeQuestion
     *
     * @return \HopitalNumerique\RechercheBundle\Entity\ExpBesoin $question
     */
    public function getRedirigeQuestion()
    {
        return $this->redirigeQuestion;
    }
    
    /**
     * Set redirigeQuestion
     *
     * @param \HopitalNumerique\RechercheBundle\Entity\ExpBesoin $question
     */
    public function setRedirigeQuestion(\HopitalNumerique\RechercheBundle\Entity\ExpBesoin $expBesoin)
    {
        $this->redirigeQuestion = $expBesoin;
    }
}
