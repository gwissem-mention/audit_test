<?php

namespace HopitalNumerique\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nodevo\ContactBundle\Entity\Contact as NodevoContact;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;


/**
 * Contact
 *
 * @ORM\Table("hn_contact")
 * @ORM\Entity(repositoryClass="HopitalNumerique\ContactBundle\Repository\ContactRepository")
 */
class Contact extends NodevoContact
{
    /**
     * @var string
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans la fonction de la structure.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans la fonction de la structure."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[255]]")
     * @ORM\Column(name="contact_fonction_strucutre", type="string", length=255, nullable=true, options = {"comment" = "Fonction au sein de la structure"})
     */
    protected $fonctionStructure;
    
    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_civilite", referencedColumnName="ref_id")
     * @Assert\NotBlank(message="La civilité ne peut pas être vide.")
     * @Nodevo\Javascript(class="validate[required]")
     */
    protected $civilite;
    
    /**
     * @var string
     * 
     * @Assert\NotBlank(message="Le nom ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "50",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[50]]")
     * @ORM\Column(name="contact_nom", type="string", length=50)
     */
    protected $codepostal;

    /**
     * Get fonctionStructure
     *
     * @return string $fonctionStructure
     */
    public function getFonctionStructure()
    {
        return $this->fonctionStructure;
    }
    
    /**
     * Set fonctionStructure
     *
     * @param string $fonctionStructure
     */
    public function setFonctionStructure($fonctionStructure)
    {
        $this->fonctionStructure = $fonctionStructure;
    }
    
    /**
     * Get civilite
     *
     * @return \HopitalNumerique\ReferenceBundle\Entity\Reference $civilite
     */
    public function getCivilite()
    {
        return $this->civilite;
    }
    
    /**
     * Set civilite
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $civilite
     */
    public function setCivilite($civilite)
    {
        if($civilite instanceof \HopitalNumerique\ReferenceBundle\Entity\Reference )
            $this->civilite = $civilite;
        else
            $this->civilite = null;
    }
}
