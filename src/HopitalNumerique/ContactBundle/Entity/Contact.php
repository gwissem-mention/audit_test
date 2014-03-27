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
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="id",
 *          column=@ORM\Column(
 *              name     = "contact_id",
 *              type     = "integer",
 *              options  = {"comment" = "Identifiant du contact"}
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="prenom",
 *          column=@ORM\Column(
 *              name     = "contact_prenom",
 *              type     = "string",
 *              length   = 50,
 *              options  = {"comment" = "Prenom"}
 *          )
 *      ),
 * })
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
}
