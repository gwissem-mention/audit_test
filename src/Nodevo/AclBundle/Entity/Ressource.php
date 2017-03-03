<?php

namespace Nodevo\AclBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ressource.
 *
 * @ORM\Table(name="core_ressource")
 * @ORM\Entity(repositoryClass="Nodevo\AclBundle\Repository\RessourceRepository")
 */
class Ressource
{
    /**
     * @var int
     *
     * @ORM\Column(name="res_id", type="integer", options = {"comment" = "ID de la ressource"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="res_nom", type="string", length=255, options = {"comment" = "Nom de la ressource"})
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="res_pattern", type="string", length=255, options = {"comment" = "Patron de regex de l URL"})
     */
    private $pattern;

    /**
     * @var int
     *
     * @ORM\Column(name="res_order", type="integer", options = {"comment" = "Ordre de la ressource"})
     */
    private $order;

    /**
     * @var int
     *
     * @ORM\Column(name="res_type", type="integer", options = {"comment" = "Type de la ressource : 1 multiple, 2 simple", "default" : "1"})
     */
    private $type;

    public function __construct()
    {
        $this->order = 1;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nom.
     *
     * @param string $nom
     *
     * @return Ressource
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom.
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Get pattern.
     *
     * @return string $pattern
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Set pattern.
     *
     * @param string $pattern
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Get order.
     *
     * @return int $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order.
     *
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Set type.
     *
     * @param int $type
     *
     * @return Ressource
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
}
