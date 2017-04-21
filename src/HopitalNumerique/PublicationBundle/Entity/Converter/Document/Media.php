<?php

namespace HopitalNumerique\PublicationBundle\Entity\Converter\Document;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Media
 * @package HopitalNumerique\PublicationBundle\Entity\Converter
 *
 * @ORM\Table(name="hn_converter_media")
 * @ORM\Entity()
 */
class Media
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="original_path", type="string")
     */
    private $originalPath;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string")
     * @TODO Rename to public (absolute) path
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     * @Assert\Length(max = 50)
     */
    private $name;


    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $excluded = false;

    /**
     * @var Node
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node", inversedBy="medias")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $node;

    /**
     * Media constructor.
     * @param string $path
     */
    public function __construct(Node $node, $path, $name = null)
    {
        $this->node = $node;
        $this->originalPath = $path;
        $this->path = $path;
        $this->name = $name;
    }

    /**
     * @param NodeInterface $node
     *
     * @param $originalePath
     * @param $path
     * @return Media
     */
    public static function createForNode(NodeInterface $node, $originalePath, $path)
    {
        $media = new self($node, $path);
        $media->originalPath = $originalePath;
        $node->addMedia($media);

        return $media;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOriginalPath()
    {
        return $this->originalPath;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Node
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @return bool
     */
    public function isExcluded()
    {
        return $this->excluded;
    }

    /**
     * @param bool $excluded
     *
     * @return Media
     */
    public function setExcluded($excluded)
    {
        $this->excluded = $excluded;

        return $this;
    }
}
