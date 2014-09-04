<?php
/**
 * Entité d'un élément de restitution par processus.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace HopitalNumerique\AutodiagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * Constructeur de Process.
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


    public function getChapitres()
    {
        $chapitres = array();
        
        if ($this->outil != null)
        {
            foreach ($this->outil->getProcessChapitres() as $processChapitre)
            {
                $chapitres[] = $processChapitre->getChapitre();
            }
        }
        
        return $chapitres;
    }
}
