<?php
namespace HopitalNumerique\ReferenceBundle\Entity\Reference;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Nom d'un champ lexical.
 *
 * @ORM\Table(name="hn_reference_champ_lexical_nom", uniqueConstraints={@ORM\UniqueConstraint(name="LIBELLE_UNIQUE", columns={"chl_libelle"})})
 * @ORM\Entity()
 */
class ChampLexicalNom
{
    /**
     * @var integer
     *
     * @ORM\Column(name="chl_id", type="integer", options={"unsigned"=true})
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="chl_libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\ManyToMany(targetEntity="HopitalNumerique\ReferenceBundle\Entity\Reference", mappedBy="champLexicalNoms")
     */
    private $references;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->references = new \Doctrine\Common\Collections\ArrayCollection();
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
     *
     * @return ChampLexicalNom
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
     * Add reference
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference
     *
     * @return ChampLexicalNom
     */
    public function addReference(\HopitalNumerique\ReferenceBundle\Entity\Reference $reference)
    {
        $this->references[] = $reference;

        return $this;
    }

    /**
     * Remove reference
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference $reference
     */
    public function removeReference(\HopitalNumerique\ReferenceBundle\Entity\Reference $reference)
    {
        $this->references->removeElement($reference);
    }

    /**
     * Get references
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReferences()
    {
        return $this->references;
    }


    /**
     * Retourne l'égalité entre deux champLexicalNom.
     *
     * @param \HopitalNumerique\ReferenceBundle\Entity\Reference\ChampLexicalNom $champLexicalNom Autre champLexicalNom
     * @return boolean Si égalité
     */
    public function equals(ChampLexicalNom $champLexicalNom)
    {
        return ($this->id === $champLexicalNom->getId());
    }
}
