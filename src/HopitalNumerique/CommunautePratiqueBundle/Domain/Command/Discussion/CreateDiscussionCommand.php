<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

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

        if ($object) {
            $this->object = $object;
            $this->title = $object->getTitre();
        }
    }
}
