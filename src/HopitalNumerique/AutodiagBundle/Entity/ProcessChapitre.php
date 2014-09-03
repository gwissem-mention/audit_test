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
}
