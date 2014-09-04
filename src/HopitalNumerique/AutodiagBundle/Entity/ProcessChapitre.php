<?php
/**
 * Entité de liaison entre un outil de diagnostique et un chapitre s'il y a restitution par processus.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entité de liaison entre un outil de diagnostique et un chapitre s'il y a restitution par processus.
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
     * @var \HopitalNumerique\AutodiagBundle\Entity\Outil Outil de diagnostic
     * 
     * @ORM\ManyToOne
     * (
     *   targetEntity = "Outil",
     *   cascade = { "persist" },
     *   inversedBy = "processChapitres"
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
     * @var \HopitalNumerique\AutodiagBundle\Entity\Outil Chapitre d'un outil de diagnostic
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
     * @var integer Ordre d'affichage du chapitre dans l'outil de diagnostique.
     * 
     * @ORM\Column(
     *   name = "order",
     *   type = "integer",
     *   nullable = false,
     *   options =
     *   {
     *     "unsigned" = true,
     *     "comment" = "Ordre d affichage du chapitre dans l outil"
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
     * Set outil
     *
     * @param \HopitalNumerique\AutodiagBundle\Entity\Outil $outil
     * @return \HopitalNumerique\AutodiagBundle\Entity\ProcessChapitre
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
