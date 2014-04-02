<?php

namespace Nodevo\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

//Asserts Stuff
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Contact
 * 
 * @ORM\MappedSuperclass
 */
class Contact
{
    /**
     * @var integer
     *
     * @ORM\Column(name="contact_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Le prénom ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "50",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le prénom.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le prénom."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[50]]")
     * @ORM\Column(name="contact_prenom", type="string", length=50, options = {"comment" = "Prénom de la personne qui a contacté"})
     */
    protected $prenom;

    /**
     * @var string
     * 
     *@Assert\NotBlank(message="Le nom ne peut pas être vide.")
     * @Assert\Length(
     *      min = "1",
     *      max = "50",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom."
     * )
     * @Nodevo\Javascript(class="validate[required,minSize[1],maxSize[50]]")
     * @ORM\Column(name="contact_nom", type="string", length=50)
     */
    protected $nom;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="L'adresse éléctronique ne peut pas être vide.")
     * @Assert\Regex(pattern= "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]{2,}[.][a-zA-Z]{2,3}$/")
     * @Assert\Length(
     *      min = "1",
     *      max = "50",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le nom de compte.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le nom de compte."
     * )
     * @Nodevo\Javascript(class="validate[required,custom[email]]")
     * @ORM\Column(name="contact_mail", type="string", length=255)
     */
    protected $mail;

    /**
     * @var string
     * 
     * @Assert\Length(
     *      max = "50",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le pays."
     * )
     * @Nodevo\Javascript(class="validate[maxSize[50]]")
     * @ORM\Column(name="contact_pays", type="string", length=50)
     */
    protected $pays;

    /**
     * @var string
     * 
     * @Assert\Length(
     *      max = "50",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans la ville."
     * )
     * @Nodevo\Javascript(class="validate[maxSize[50]]")
     * @ORM\Column(name="contact_ville", type="string", length=50)
     */
    protected $ville;

    /**
     * @var string
     * @Assert\Length(
     *      min = "14",
     *      max = "14",
     *      minMessage="Le numéro de téléphone direct doit être respecter le format XX XX XX XX XX.",
     *      maxMessage="Le numéro de téléphone direct doit être respecter le format XX XX XX XX XX."
     * )
     * @Nodevo\Javascript(class="validate[minSize[14],maxSize[14]],custom[phone]", mask="99 99 99 99 99")
     * @ORM\Column(name="contact_telephone", type="string", length=14, nullable=true, options = {"comment" = "Téléphone de l utilisateur"})
     */
    protected $telephone;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="Le massage de contact ne doit pas être vide.")
     * @Nodevo\Javascript(class="validate[required]]")
     * @ORM\Column(name="contact_message", type="text")
     */
    protected $message;


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
     * Set prenom
     *
     * @param string $prenom
     * @return Contact
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string 
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Contact
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set mail
     *
     * @param string $mail
     * @return Contact
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail
     *
     * @return string 
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set ville
     *
     * @param string $ville
     * @return Contact
     */
    public function setVille($ville)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville
     *
     * @return string 
     */
    public function getVille()
    {
        return $this->ville;
    }
    
    /**
     * Set pays
     *
     * @param string $pays
     * @return Contact
     */
    public function setPays($pays)
    {
        $this->pays = pays;
    
        return $this;
    }
    
    /**
     * Get pays
     *
     * @return string
     */
    public function getPays()
    {
        return $this->pays;
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return Contact
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get telephone
     *
     * @return string 
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Contact
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }
}
