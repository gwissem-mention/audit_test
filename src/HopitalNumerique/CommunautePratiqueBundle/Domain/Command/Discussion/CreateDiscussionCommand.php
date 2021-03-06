<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use Symfony\Component\Validator\Constraints as Assert;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;

/**
 * Class CreateDiscussionCommand
 */
class CreateDiscussionCommand
{
    /**
     * @var User $author
     */
    public $author;

    /**
     * @var Groupe $group
     */
    public $group;

    /**
     * @var string $title
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     */
    public $title;

    /**
     * @var string $content
     * @Assert\NotBlank()
     */
    public $content;

    /**
     * @var array $files
     */
    public $files;

    /**
     * @var Domaine[]
     */
    public $domains;

    /**
     * @var Objet $object
     */
    public $object;

    /**
     * @var int
     */
    public $source;

    /**
     * CreateDiscussionCommand constructor.
     *
     * @param User $author
     * @param array $domains
     * @param Groupe|null $group
     * @param Objet $object
     */
    public function __construct(User $author, array $domains, Groupe $group = null, Objet $object = null)
    {
        $this->author = $author;
        $this->group = $group;
        $this->domains = $domains;

        if ($group) {
            $this->source = Discussion::CREATED_IN_GROUP;
        } else {
            $this->source = Discussion::CREATED_AS_PUBLIC;
        }

        if ($object) {
            $this->object = $object;
            $this->title = $object->getTitre();
        }
    }
}
