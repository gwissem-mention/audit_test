<?php

namespace HopitalNumerique\PublicationBundle\Entity\Converter\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class Document
 * @package HopitalNumerique\PublicationBundle\Entity\Converter
 *
 * @ORM\Table(name="hn_converter_node")
 * @ORM\Entity()
 */
class Node implements NodeInterface
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
     * @ORM\Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $content;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $footnotes = [];

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $excluded = false;

    /**
     * @var Node
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $squashIn;

    /**
     * @var Node
     *
     * @ORM\ManyToOne(targetEntity="HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node", inversedBy="childrens", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @var Node[]
     *
     * @ORM\OneToMany(targetEntity="HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node", mappedBy="parent", cascade={"persist"})
     */
    protected $childrens;

    /**
     * @var File[]
     */
    protected $files;

    /**
     * @var ArrayCollection|Media[]
     *
     * @ORM\OneToMany(targetEntity="HopitalNumerique\PublicationBundle\Entity\Converter\Document\Media", mappedBy="node", cascade={"persist"})
     */
    protected $medias;

    /**
     * Node constructor.
     * @param $title
     */
    public function __construct($title = null)
    {
        $this->title = $title;
        $this->files = [];
        $this->medias = new ArrayCollection();
        $this->childrens = [];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $text
     * @return $this
     */
    public function appendContent($text)
    {
        $this->content .= $text;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return array
     */
    public function getFootnotes()
    {
        return $this->footnotes;
    }

    /**
     * @param $id
     * @param $note
     *
     * @return Node
     */
    public function addFootnote($id, $note)
    {
        $this->footnotes[$id] = $note;

        return $this;
    }

    public function getHighestParent()
    {
        return null !== $this->parent ? $this->parent->getHighestParent() : $this;
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
     */
    public function setExcluded($excluded)
    {
        $this->excluded = $excluded;
    }

    /**
     * @return \HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node
     */
    public function getSquashIn()
    {
        return $this->squashIn;
    }

    /**
     * @param \HopitalNumerique\PublicationBundle\Entity\Converter\Document\Node $squashIn
     */
    public function setSquashIn(NodeInterface$squashIn = null)
    {
        $this->squashIn = $squashIn;
    }

    /**
     * @return Node
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function unsetParent()
    {
        if (null !== $this->parent) {
            $this->parent->removeChildren($this);
            $this->parent = null;
        }
    }

    public function setParent(NodeInterface $node)
    {
        $this->parent = $node;
        $node->addChildren($this);
    }

    public function addChildren(NodeInterface $child)
    {
        //  @TODO : To remove
//        $this->childrens[$child->getId()] = $child;
        $this->childrens[] = $child;
    }

    public function removeChildren(NodeInterface $node)
    {
        $this->childrens->removeElement($node);
    }

    public function getChildrens()
    {
        return $this->childrens;
    }

    public function getDeep()
    {
        return null === $this->parent
            ? 0
            : 1 + $this->parent->getDeep()
        ;
    }

    /**
     * @return File[]
     */
    public function getFiles($deep = false)
    {
        $medias = $this->files;

        if ($deep) {
            foreach ($this->getChildrens() as $children) {
                $medias = array_merge($medias, $children->getFiles(true));
            }
        }

        return $medias;
    }

    /**
     * @param File $file
     */
    public function addFile(File $file)
    {
        $this->files[] = $file;
    }

    public function addMedia(Media $media)
    {
        $this->medias[$media->getOriginalPath()] = $media;
    }

    /**
     * @param bool $deep
     * @return ArrayCollection|Media[]
     */
    public function getMedias($deep = false)
    {
        $medias = $this->medias->toArray();

        if ($deep) {
            foreach ($this->getChildrens() as $children) {
                $medias = array_merge($medias, $children->getMedias(true)->toArray());
            }
        }

        return new ArrayCollection($medias);
    }

    /**
     * @param bool $deep
     *
     * @return \Doctrine\Common\Collections\Collection|Media[]
     */
    public function getExcludedMedias($deep = false)
    {
        return $this->getMedias($deep)->filter(function (Media $media) {
            return $media->isExcluded();
        });
    }
}
