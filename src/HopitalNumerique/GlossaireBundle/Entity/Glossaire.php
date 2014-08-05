<?php

namespace HopitalNumerique\GlossaireBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Glossaire
 *
 * @ORM\Table(name="hn_glossaire")
 * @ORM\Entity(repositoryClass="HopitalNumerique\GlossaireBundle\Repository\GlossaireRepository")
 */
class Glossaire
{
    /**
     * @var integer
     *
     * @ORM\Column(name="glo_id", type="integer", options = {"comment" = "ID du glossaire"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Le mot ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le mot.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le mot."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[255]]")
     * @ORM\Column(name="glo_mot", type="string", length=255, options = {"comment" = "Mot du glossaire"})
     */
    private $mot;

    /**
     * @var string
     *
     * @ORM\Column(name="glo_intitule", type="text", nullable=true, options = {"comment" = "Intitule du glossaire"})
     */
    private $intitule;

    /**
     * @var string
     *
     * @ORM\Column(name="glo_description", type="text", nullable=true, options = {"comment" = "Description du glossaire"})
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_statut", referencedColumnName="ref_id")
     * @Nodevo\Javascript(class="validate[required]")
     */
    protected $etat;

    /**
     * Initialisation de l'entitée (valeurs par défaut)
     */
    public function __construct()
    {
        $this->intitule    = null;
        $this->description = null;
        $this->etat        = 3;
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
     * Set mot
     *
     * @param string $mot
     * @return Glossaire
     */
    public function setMot($mot)
    {
        $this->mot = $mot;

        return $this;
    }

    /**
     * Get mot
     *
     * @return string 
     */
    public function getMot()
    {
        return $this->mot;
    }

    /**
     * Set intitule
     *
     * @param string $intitule
     * @return Glossaire
     */
    public function setIntitule($intitule)
    {
        $this->intitule = $intitule;

        return $this;
    }

    /**
     * Get intitule
     *
     * @return string 
     */
    public function getIntitule()
    {
        return $this->intitule;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Glossaire
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get etat
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $etat
     */
    public function getEtat()
    {
        return $this->etat;
    }
    
    /**
     * Set etat
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $etat
     */
    public function setEtat(\HopitalNumerique\ReferenceBundle\Entity\Reference $etat)
    {
        $this->etat = $etat;
    }
}
