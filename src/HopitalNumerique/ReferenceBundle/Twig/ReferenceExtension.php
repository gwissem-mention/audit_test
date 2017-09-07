<?php

namespace HopitalNumerique\ReferenceBundle\Twig;

use HopitalNumerique\ReferenceBundle\Manager\ReferenceManager;
use HopitalNumerique\ReferenceBundle\Repository\ReferenceRepository;

class ReferenceExtension extends \Twig_Extension
{
    /**
     * References manager
     *
     * @var ReferenceManager
     */
    private $referenceManager;

    /**
     * References repository
     *
     * @var ReferenceRepository
     */
    private $referenceRepository;

    /**
     * Construit l'extension Twig en lui passant les 2 managers requis pour la checkAuthorization.
     *
     * @param ReferenceManager $referenceManager Le manager des références
     * @param ReferenceRepository $referenceRepository Le repository des références
     */
    public function __construct(ReferenceManager $referenceManager, ReferenceRepository $referenceRepository)
    {
        $this->referenceManager = $referenceManager;
        $this->referenceRepository = $referenceRepository;
    }

    /**
     * Retourne la liste des filtres custom pour cette extension.
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            'reorder' => new \Twig_Filter_Method($this, 'reorder'),
            'getReferenceText' => new \Twig_Filter_Method($this, 'getReferenceText'),
        ];
    }

    /**
     * Vérifie que l'user à bien l'accès à la route.
     *
     * @param array $options Tableau d'options
     *
     * @return bool
     */
    public function reorder($options)
    {
        return $this->referenceManager->reorder($options);
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services.
     *
     * @return string
     */
    public function getName()
    {
        return 'ref_extension';
    }

    /**
     * Returns text related to reference id.
     *
     * @param  string $referenceId Id of reference.
     *
     * @return string Text of found reference or empty string.
     */
    public function getReferenceText($referenceId)
    {
        $ref = $this->referenceRepository->findOneBy(['id' => $referenceId]);

        return $ref ? $ref->getLibelle() : '';
    }
}
