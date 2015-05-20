<?php

namespace HopitalNumerique\RechercheBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExpBesoinGestion
 *
 * @ORM\Table(name="hn_recherche_expbesoin_gestionnaire")
 * @ORM\Entity(repositoryClass="HopitalNumerique\RechercheBundle\Repository\ExpBesoinGestionRepository")
 */
class ExpBesoinGestion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="expbg_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="expbg_nom", type="string", length=255)
     */
    protected $nom;

    /**
     * @ORM\ManyToMany(targetEntity="\HopitalNumerique\DomaineBundle\Entity\Domaine")
     * @ORM\JoinTable(name="hn_domaine_gestions_recherche_aidee",
     *      joinColumns={ @ORM\JoinColumn(name="expbg_id", referencedColumnName="expbg_id", onDelete="CASCADE")},
     *      inverseJoinColumns={ @ORM\JoinColumn(name="dom_id", referencedColumnName="dom_id", onDelete="CASCADE")}
     * )
     */
    protected $domaines;

    /**
     * @ORM\OneToMany(targetEntity="ExpBesoin", mappedBy="expBesoinGestion")
     */
    protected $expBesoins;


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
     * Set nom
     *
     * @param string $nom
     * @return ExpBesoinGestion
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
     * Constructor
     */
    public function __construct()
    {
        $this->domaines = new \Doctrine\Common\Collections\ArrayCollection();
        $this->expBesoins = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add domaines
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     * @return ExpBesoinGestion
     */
    public function addDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines[] = $domaines;

        return $this;
    }

    /**
     * Remove domaines
     *
     * @param \HopitalNumerique\DomaineBundle\Entity\Domaine $domaines
     */
    public function removeDomaine(\HopitalNumerique\DomaineBundle\Entity\Domaine $domaines)
    {
        $this->domaines->removeElement($domaines);
    }

    /**
     * Get domaines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDomaines()
    {
        return $this->domaines;
    }

    /**
     * Add expBesoins
     *
     * @param \HopitalNumerique\RechercheBundle\Entity\ExpBesoin $expBesoins
     * @return ExpBesoinGestion
     */
    public function addExpBesoin(\HopitalNumerique\RechercheBundle\Entity\ExpBesoin $expBesoins)
    {
        $this->expBesoins[] = $expBesoins;

        return $this;
    }

    /**
     * Remove expBesoins
     *
     * @param \HopitalNumerique\RechercheBundle\Entity\ExpBesoin $expBesoins
     */
    public function removeExpBesoin(\HopitalNumerique\RechercheBundle\Entity\ExpBesoin $expBesoins)
    {
        $this->expBesoins->removeElement($expBesoins);
    }

    /**
     * Get expBesoins
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getExpBesoins()
    {
        return $this->expBesoins;
    }
}
