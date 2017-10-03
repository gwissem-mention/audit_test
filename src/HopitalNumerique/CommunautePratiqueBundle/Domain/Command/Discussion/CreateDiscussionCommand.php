<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Domain\Command\Discussion;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;
use HopitalNumerique\CommunautePratiqueBundle\Entity\Groupe;
use Symfony\Component\Validator\Constraints as Assert;

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
     * CreateDiscussionCommand constructor.
     *
     * @param User $author
     * @param array $domains
     * @param Groupe|null $group
     */
    public function __construct(User $author, array $domains, Groupe $group = null)
    {
        $this->author = $author;
        $this->group = $group;
        $this->domains = $domains;
    }
}
