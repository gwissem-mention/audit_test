<?php
namespace HopitalNumerique\FichierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité Extension.
 *
 * @ORM\Entity()
 * @ORM\Table(name="hn_fichier_extension")
 */
class Extension
{
    /**
     * @var integer
     *
     * @ORM\Column(name="ext_id", type="smallint", nullable=false, options={"unsigned":true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ext_valeur", type="string", nullable=false, length=8)
     * @Assert\NotBlank()
     */
    private $valeur;

    /**
     * @var \HopitalNumerique\FichierBundle\Entity\FichierType
     *
     * @ORM\ManyToOne(targetEntity="FichierType", inversedBy="extensions")
     * @ORM\JoinColumn(name="fictyp_id", referencedColumnName="fictyp_id", nullable=false, onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $fichierType;


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
     * Set valeur
     *
     * @param string $valeur
     * @return Extension
     */
    public function setValeur($valeur)
    {
        $this->valeur = $valeur;

        return $this;
    }

    /**
     * Get valeur
     *
     * @return string 
     */
    public function getValeur()
    {
        return $this->valeur;
    }

    /**
     * Set fichierType
     *
     * @param \HopitalNumerique\FichierBundle\Entity\FichierType $fichierType
     * @return Extension
     */
    public function setFichierType(\HopitalNumerique\FichierBundle\Entity\FichierType $fichierType)
    {
        $this->fichierType = $fichierType;

        return $this;
    }

    /**
     * Get fichierType
     *
     * @return \HopitalNumerique\FichierBundle\Entity\FichierType 
     */
    public function getFichierType()
    {
        return $this->fichierType;
    }
}
