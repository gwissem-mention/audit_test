<?php

namespace HopitalNumerique\PublicationBundle\Entity\Converter;

use Doctrine\Common\Collections\ArrayCollection;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\PublicationBundle\Entity\Converter\Document\NodeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Document
 * @package HopitalNumerique\PublicationBundle\Entity\Converter
 *
 * @ORM\Table(name="hn_converter_document")
 * @ORM\Entity(repositoryClass="HopitalNumerique\PublicationBundle\Repository\Converter\DocumentRepository")
 */
class Document
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var NodeInterface
     *
     * @ORM\ManyToOne(
     *     targetEntity="\HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node",
     *     cascade={"persist", "remove"},
     * )
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $tree;

    /**
     * @var Objet
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\ObjetBundle\Entity\Objet")
     * @ORM\JoinColumn(referencedColumnName="obj_id")
     */
    protected $publication;

    /**
     * Document constructor.
     * @param Objet $publication
     */
    public function __construct(Objet $publication)
    {
        $this->publication = $publication;
        $this->medias = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setTree(NodeInterface $node)
    {
        $this->tree = $node;

        return $this;
    }

    public function unsetTree()
    {
        $this->tree = null;

        return $this;
    }

    /**
     * @return NodeInterface
     */
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * @return Objet
     */
    public function getPublication()
    {
        return $this->publication;
    }
}
