<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command;

use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\Validator\Constraints as Assert;

class EditGuidedSearchConfigCommand
{
    /**
     * @var int
     */
    public $rechercheParcoursGestionId;

    /**
     * @var boolean
     */
    public $update = false;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    public $name;

    /**
     * @var Domaine[]
     *
     * @Assert\NotBlank
     */
    public $domaines = [];

    /**
     * @var array
     */
    public $publicationsType = [];

    /**
     * @var Reference[]
     *
     * @Assert\Count(min=1, groups={"update"})
     */
    public $referencesParentes = [];

    /**
     * @var Reference[]
     *
     * @Assert\Count(min=1, groups={"update"})
     */
    public $referencesVentilations = [];
}
