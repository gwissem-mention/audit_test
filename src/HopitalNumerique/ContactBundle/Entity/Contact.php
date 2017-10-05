<?php

namespace HopitalNumerique\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HopitalNumerique\EtablissementBundle\Entity\Etablissement;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Nodevo\ContactBundle\Entity\Contact as NodevoContact;
use Symfony\Component\Validator\Constraints as Assert;
use Nodevo\ToolsBundle\Validator\Constraints as Nodevo;

/**
 * Contact.
 *
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
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
     * @ORM\JoinColumn(name="ref_region", referencedColumnName="ref_id", nullable=true)
     */
    protected $region;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_departement", referencedColumnName="ref_id", nullable=true)
     */
    protected $departement;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\ReferenceBundle\Entity\Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="ref_statut_etablissement_sante", referencedColumnName="ref_id")
     */
    protected $statutEtablissementSante;

    /**
     * @ORM\ManyToOne(targetEntity="\HopitalNumerique\EtablissementBundle\Entity\Etablissement", cascade={"persist"})
     * @ORM\JoinColumn(name="eta_etablissement_rattachement_sante", referencedColumnName="eta_id")
     */
    protected $etablissementRattachementSante;

    /**
     * @var string
     * @Assert\Length(
     *      min = "1",
     *      max = "255",
     *      minMessage="Il doit y avoir au moins {{ limit }} caractères dans le Nom de votre structure si non disponible dans la liste précédente.",
     *      maxMessage="Il doit y avoir au maximum {{ limit }} caractères dans le Nom de votre structure si non disponible dans la liste précédente."
     * )
     * @Nodevo\Javascript(class="validate[minSize[1],maxSize[255]]")
     * @ORM\Column(name="usr_autre_rattachement_sante", type="string", length=255, nullable=true, options = {"comment" = "Nom de votre structure si non disponible dans la liste précédente santé de l utilisateur"})
     */
    protected $autreStructureRattachementSante;

    /**
     * Get fonctionStructure.
     *
     * @return string $fonctionStructure
     */
    public function getFonctionStructure()
    {
        return $this->fonctionStructure;
    }

    /**
     * Set fonctionStructure.
     *
     * @param string $fonctionStructure
     */
    public function setFonctionStructure($fonctionStructure)
    {
        $this->fonctionStructure = $fonctionStructure;
    }

    /**
     * Get region.
     *
     * @return Reference $region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set region.
     *
     * @param Reference $region
     */
    public function setRegion($region)
    {
        $this->region = $region instanceof Reference ? $region : null;
    }

    /**
     * Get département.
     *
     * @return Reference $county
     */
    public function getDepartement()
    {
        return $this->departement;
    }

    /**
     * Set département.
     *
     * @param Reference $departement
     */
    public function setDepartement($departement)
    {
        $this->departement = $departement instanceof Reference ? $departement : null;
    }

    /**
     * Set statutEtablissementSante.
     *
     * @param Reference $statutEtablissementSante
     */
    public function setStatutEtablissementSante($statutEtablissementSante)
    {
        $this->statutEtablissementSante = $statutEtablissementSante instanceof Reference ? $statutEtablissementSante
            : null;
    }

    /**
     * Get statutEtablissementSante.
     *
     * @return Reference $statutEtablissementSante
     */
    public function getStatutEtablissementSante()
    {
        return $this->statutEtablissementSante;
    }

    /**
     * Get etablissementRattachementSante.
     *
     * @return string $etablissementRattachementSante
     */
    public function getEtablissementRattachementSante()
    {
        return $this->etablissementRattachementSante;
    }

    /**
     * Set etablissementRattachementSante.
     *
     * @param string $etablissementRattachementSante
     */
    public function setEtablissementRattachementSante($etablissementRattachementSante)
    {
        $this->etablissementRattachementSante = $etablissementRattachementSante instanceof Etablissement
            ? $etablissementRattachementSante : null;
    }

    /**
     * Get autreStructureRattachementSante.
     *
     * @return string $autreStructureRattachementSante
     */
    public function getAutreStructureRattachementSante()
    {
        return $this->autreStructureRattachementSante;
    }

    /**
     * Set autreStructureRattachementSante.
     *
     * @param string $autreStructureRattachementSante
     */
    public function setAutreStructureRattachementSante($autreStructureRattachementSante)
    {
        $this->autreStructureRattachementSante = $autreStructureRattachementSante;
    }
}
