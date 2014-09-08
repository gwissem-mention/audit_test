<?php
/**
 * Entité de liaison entre un process de diagnostique et un chapitre s'il y a restitution par processus.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entité de liaison entre un process de diagnostique et un chapitre s'il y a restitution par processus.
 * 
 * @ORM\Entity
 * @ORM\Table(name="hn_outil_process_chap")
 */
class ProcessChapitre
{
    /**
     * @var integer Id
     * 
     * @ORM\Id
     * @ORM\Column(
     *   name = "proc_cha_id",
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
     * @var \HopitalNumerique\AutodiagBundle\Entity\Process Process de diagnostic
     * 
     * @ORM\ManyToOne
     * (
     *   targetEntity = "Process",
     *   cascade = { "persist" },
     *   inversedBy = "processChapitres"
     * )
     * @ORM\JoinColumn
     * (
     *   name = "proc_id",
     *   referencedColumnName = "proc_id",
     *   onDelete = "CASCADE"
     * )
     */
    private $process;

    /**
     * @var \HopitalNumerique\AutodiagBundle\Entity\Process Chapitre d'un process de diagnostic
     * 
     * @ORM\ManyToOne
     * (
     *   targetEntity = "Chapitre",
     *   cascade = { "persist" },
     *   inversedBy = "processChapitres"
     * )
     * @ORM\JoinColumn
     * (
     *   name = "chap_id",
     *   referencedColumnName = "cha_id",
     *   onDelete = "CASCADE"
     * )
     */
    private $chapitre;
    
    /**
     * @var integer Ordre d'affichage du chapitre dans l'process de diagnostique.
     * 
     * @ORM\Column(
     *   name = "proc_cha_order",
     *   type = "integer",
     *   nullable = false,
     *   options =
     *   {
     *     "unsigned" = true,
     *     "comment" = "Ordre d affichage du chapitre dans l process"
     *   }
     * )
     */
    private $order;
    
    
    /**
     * Constructeur de ProcessChapitre.
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
     * Set process
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Process $process
     * @return \HopitalNumerique\AutodiagBundle\Entity\ProcessChapitre
     */
    public function setProcess($process)
    {
        $this->process = $process;
    
        return $this;
    }
    
    /**
     * Get process
     *
     * @return \HopitalNumerique\AutodiagBundle\Entity\Process
     */
    public function getProcess()
    {
        return $this->process;
    }
    
    /**
     * Set chapitre
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Chapitre $chapitre
     * @return \HopitalNumerique\AutodiagBundle\Entity\ProcessChapitre
     */
    public function setChapitre($chapitre)
    {
        $this->chapitre = $chapitre;
    
        return $this;
    }
    
    /**
     * Get chapitre
     *
     * @return \HopitalNumerique\AutodiagBundle\Entity\Chapitre
     */
    public function getChapitre()
    {
        return $this->chapitre;
    }
    
    /**
     * Set order
     *
     * @param integer $order
     * @return \HopitalNumerique\AutodiagBundle\Entity\ProcessChapitre
     */
    public function setOrder($order)
    {
        $this->order = $order;
    
        return $this;
    }
    
    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }
}
