<?php

namespace HopitalNumerique\RechercheParcoursBundle\Service;

use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearch;
use HopitalNumerique\RechercheParcoursBundle\Entity\GuidedSearchStep;
use HopitalNumerique\RechercheParcoursBundle\Repository\RechercheParcoursDetailsRepository;
use HopitalNumerique\ReferenceBundle\Repository\ReferenceRepository;
use Nodevo\ToolsBundle\Tools\Chaine;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class GuidedSearchStepUrlGenerator
{
    /**
     * @var RouterInterface $router
     */
    protected $router;

    /**
     * @var RechercheParcoursDetailsRepository $parentReferenceRepository
     */
    protected $parentReferenceRepository;

    /**
     * @var ReferenceRepository $referencerepository
     */
    protected $referenceRepository;

    /**
     * GuidedSearchStepUrlGenerator constructor.
     *
     * @param RouterInterface $router
     * @param RechercheParcoursDetailsRepository $parentReferenceRepository
     * @param ReferenceRepository $referenceRepository
     */
    public function __construct(RouterInterface $router, RechercheParcoursDetailsRepository $parentReferenceRepository, ReferenceRepository $referenceRepository)
    {
        $this->router = $router;
        $this->parentReferenceRepository = $parentReferenceRepository;
        $this->referenceRepository = $referenceRepository;
    }

    /**
     * @param GuidedSearchStep $guidedSearchStep
     *
     * @return string
     */
    public function generate(GuidedSearchStep $guidedSearchStep)
    {
        return $this->generateFromGuidedSearchAndStepPath($guidedSearchStep->getGuidedSearch(), $guidedSearchStep->getStepPath());
    }

    /**
     * @param GuidedSearch $guidedSearch
     * @param              $stepPath
     *
     * @return string
     */
    public function generateFromGuidedSearchAndStepPath(GuidedSearch $guidedSearch, $stepPath, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $guidedSearchReference = $guidedSearch->getGuidedSearchReference();

        $stepPath = explode(':', $stepPath);

        $parentReference = $this->parentReferenceRepository->find($stepPath[0]);

        $subReferenceParameters = [];
        if (isset($stepPath[1]) && !is_null($subReference = $this->referenceRepository->find($stepPath[1]))) {
            $subReferenceParameters = [
                'subReference' => $subReference->getId(),
                'subAlias' => (new Chaine($subReference->getLibelle()))->minifie(),
            ];
        }

        return $this->router->generate('hopital_numerique_guided_search_step', array_merge([
                'guidedSearch' => $guidedSearch->getId(),
                'guidedSearchReference' => $guidedSearchReference->getId(),
                'guidedSearchReferenceAlias' => (new Chaine($guidedSearchReference->getReference()->getLibelle()))->minifie(),
                'parentReference' => $parentReference->getId(),
                'alias' => (new Chaine($parentReference->getReference()->getLibelle()))->minifie(),
            ], $subReferenceParameters), $referenceType)."#risk";
    }
}
