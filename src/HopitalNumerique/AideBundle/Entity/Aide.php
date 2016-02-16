<?php

namespace HopitalNumerique\AideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Aide
 *
 * @ORM\Table(name="hn_aide")
 * @ORM\Entity(repositoryClass="HopitalNumerique\AideBundle\Repository\AideRepository")
 * @UniqueEntity(fields={"route","libelle"}, message="Une aide existe déjà pour cette page.")
 */
class Aide
{

    /**
     * @var integer
     *
     * @ORM\Column(name="aide_id", type="integer", options = {"comment" = "ID de l'aide"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank(message="La route ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans la route.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans la route."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     * @ORM\Column(name="aide_route", type="string", length=255, options = {"comment" = "Route de l'aide"})
     */
    protected $route;

    /**
     * @var string
     * @Assert\NotBlank(message="Le libellé ne peut pas être vide.")
     *
     * @Nodevo\Javascript(class="validate[required,minSize[1]]")
     * @ORM\Column(name="aide_libelle", type="string", options = {"comment" = "Libellé de l'aide"})
     */
    protected $libelle;


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
     * Set route
     *
     * @param string $route
     * @return Aide
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     * @return Aide
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
}
