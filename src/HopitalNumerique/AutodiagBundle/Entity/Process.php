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
     * @ORM\ManyToOne(targetEntity="Outil", cascade={"persist"}, inversedBy="resultats")
     * @ORM\JoinColumn(name="out_id", referencedColumnName="out_id", onDelete="CASCADE")
     */
    
    
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
}
