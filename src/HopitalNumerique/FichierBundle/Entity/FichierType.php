<?php
namespace HopitalNumerique\FichierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité FichierType.
 *
 * @ORM\Entity()
 * @ORM\Table(name="hn_fichier_type")
 */
class FichierType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="fictyp_id", type="smallint", nullable=false, options={"unsigned":true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="fictypc_libelle", type="string", nullable=false, length=32)
     * @Assert\NotBlank()
     */
    private $libelle;

    /**
     * @ORM\OneToMany(targetEntity="Extension", mappedBy="fichierType")
     */
    private $extensions;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->extensions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return FichierType
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
     * Add extensions
     *
     * @param \HopitalNumerique\FichierBundle\Entity\Extension $extensions
     * @return FichierType
     */
    public function addExtension(\HopitalNumerique\FichierBundle\Entity\Extension $extensions)
    {
        $this->extensions[] = $extensions;

        return $this;
    }

    /**
     * Remove extensions
     *
     * @param \HopitalNumerique\FichierBundle\Entity\Extension $extensions
     */
    public function removeExtension(\HopitalNumerique\FichierBundle\Entity\Extension $extensions)
    {
        $this->extensions->removeElement($extensions);
    }

    /**
     * Get extensions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getExtensions()
    {
        return $this->extensions;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->libelle;
    }


    /**
     * Retourne juste les valeurs des extensions (pas les entités Doctrine).
     *
     * @return array<string> Extensions
     */
    public function getExtensionValeurs()
    {
        $extensions = array();

        foreach ($this->extensions as $extension) {
            $extensions[] = $extension->getValeur();
        }

        return $extensions;
    }
}
