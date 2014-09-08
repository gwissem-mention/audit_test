<?php
/**
 * Entité d'un élément de restitution par processus.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Entité d'un élément de restitution par processus.
 * 
 * @ORM\Entity
 * @ORM\Table(name="hn_outil_process")
 */
class Process
{
    /**
     * @var integer Id
     * 
     * @ORM\Id
     * @ORM\Column(
     *   name = "proc_id",
     *   type = "integer",
     *   nullable = false,
     *   options =
     *   {
     *     "unsigned" = true,
     *     "comment" = "Id"
     *   }
     * )
     * @ORM\GeneratedValue( strategy = "AUTO" )
     */
    private $id;

    /**
     * @var string Libellé de l'élément de restitution par processus
     *
     * @ORM\Column
     * (
     *   name = "proc_libelle",
     *   type = "string",
     *   length = 255,
     *   nullable = false,
     *   options =
     *   {
     *     "comment" = "Libellé de l élément de restitution par processus"
     *   }
     * )
     * @Assert\NotBlank(message="Le libellé ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage = "Il doit y avoir au moins {{ limit }} caractères dans le libellé.",
     *      maxMessage = "Il doit y avoir au maximum {{ limit }} caractères dans le libellé."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     */
    private $libelle;

    /**
     * @var \HopitalNumerique\AutodiagBundle\Entity\Outil Outil de diagnostic
     * 
     * @ORM\ManyToOne
     * (
     *   targetEntity = "Outil",
     *   cascade = { "persist" },
     *   inversedBy = "process"
     * )
     * @ORM\JoinColumn
     * (
     *   name = "out_id",
     *   referencedColumnName = "out_id",
     *   onDelete = "CASCADE"
     * )
     */
    private $outil;
    
    /**
     * @var \Doctrine\Common\Collections\Collection Les chapitres lors d'une restitution par process
     *
     * @ORM\OneToMany(
     *   targetEntity = "ProcessChapitre",
     *   mappedBy = "process",
     *   cascade = { "persist" }
     * )
     */
    private $processChapitres;
    
    
    /**
     * Constructeur de Process.
     */
    public function __construct()
    {
        $this->processChapitres = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return \HopitalNumerique\AutodiagBundle\Entity\Process
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
     * Set outil
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Outil $outil
     * @return \HopitalNumerique\AutodiagBundle\Entity\Process
     */
    public function setOutil($outil)
    {
        $this->outil = $outil;
    
        return $this;
    }
    
    /**
     * Get outil
     *
     * @return \HopitalNumerique\AutodiagBundle\Entity\Outil
     */
    public function getOutil()
    {
        return $this->outil;
    }

    /**
     * Get processChapitres
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getProcessChapitres()
    {
        return $this->processChapitres;
    }
    
    /**
     * Set processChapitres
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $processChapitres
     */
    public function setProcessChapitres(\Doctrine\Common\Collections\ArrayCollection $processChapitres)
    {
        $this->processChapitres = $processChapitres;
        return $this;
    }
    
    public function addProcessChapitre(ProcessChapitre $processChapitre)
    {
        $this->processChapitres->add($processChapitre);
    }

    
    public function getChapitres()
    {
        $chapitres = array();

        foreach ($this->processChapitres as $processChapitre)
        {
            $chapitres[] = $processChapitre->getChapitre();
        }

        return $chapitres;
    }
    public function addChapitre(Chapitre $chapitre)
    {
        $processChapitre = new ProcessChapitre();
        $processChapitre->setProcess($this);
        $processChapitre->setChapitre($chapitre);
        $processChapitre->setOrder(count($this->processChapitres) + 1);
        
        $this->addProcessChapitre($processChapitre);
    }
    public function removeChapitre(Chapitre $chapitre)
    {
        $this->removeElement($chapitre);
    }
}
