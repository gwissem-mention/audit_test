<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use HopitalNumerique\ObjetBundle\Repository\RiskRepository;
use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use HopitalNumerique\ReferenceBundle\Entity\EntityHasReference;
use HopitalNumerique\ReferenceBundle\Repository\ReferenceRepository;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;

class AddPrivateRiskHandler
{
    /**
     * @var RiskRepository $riskRepository
     */
    protected $riskRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * @var CurrentDomaine $currentDomain
     */
    protected $currentDomain;

    /**
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * AddPrivateRiskHandler constructor.
     *
     * @param RiskRepository $riskRepository
     * @param EntityManagerInterface $entityManagerInterface
     * @param CurrentDomaine $currentDomain
     * @param ReferenceRepository $referenceRepository
     * @param ValidatorInterface $validator
     */
    public function __construct(
        RiskRepository $riskRepository,
        EntityManagerInterface $entityManager,
        CurrentDomaine $currentDomain,
        ReferenceRepository $referenceRepository,
        ValidatorInterface $validator
    ) {
        $this->riskRepository = $riskRepository;
        $this->entityManager = $entityManager;
        $this->currentDomain = $currentDomain;
        $this->referenceRepository = $referenceRepository;
        $this->validator = $validator;
    }

    /**
     * @param AddPrivateRiskCommand $addPrivateRiskCommand
     */
    public function handle(AddPrivateRiskCommand $addPrivateRiskCommand)
    {
        $risk = $this->getOrCreateRisk($addPrivateRiskCommand);

        $addPrivateRiskCommand->guidedSearchStep->getGuidedSearch()->addPrivateRisk($risk);

        if (!is_null($addPrivateRiskCommand->user)) {
            $risk->addOwner($addPrivateRiskCommand->user);
        }

        $this->entityManager->flush();

        $this->addReference(
            $risk,
            $addPrivateRiskCommand->guidedSearchStep->getGuidedSearch()->getGuidedSearchReference()->getReference()
        );

        if ($reference = $this->referenceRepository->find($addPrivateRiskCommand->guidedSearchStep->getThinnestReferenceId())) {
            $this->addReference($risk, $reference);
        }
    }

    /**
     * Refeference risk
     *
     * @param Risk $risk
     * @param Reference $reference
     */
    private function addReference(Risk $risk, Reference $reference)
    {
        $mappedReference = (new EntityHasReference())
            ->setReference($reference)
            ->setEntityId($risk->getId())
            ->setEntityType(Entity::ENTITY_TYPE_RISK)
            ->setPrimary(false)
        ;

        if (!$this->validator->validate($mappedReference, null, ['insert'])->count()) {
            $this->entityManager->persist($mappedReference);
            $this->entityManager->flush();
        }
    }

    /**
     * @param AddPrivateRiskCommand $addPrivateRiskCommand
     *
     * @return Risk
     */
    private function getOrCreateRisk(AddPrivateRiskCommand $addPrivateRiskCommand)
    {
        $risk = $this->riskRepository->findOneBy(['nature' => $addPrivateRiskCommand->nature, 'label' => $addPrivateRiskCommand->label]);

        if (is_null($risk)) {
            $risk = Risk::createPrivate()
                ->setLabel($addPrivateRiskCommand->label)
                ->setNature($addPrivateRiskCommand->nature)
                ->addDomain($this->currentDomain->get())
            ;
            $this->entityManager->persist($risk);
        }

        return $risk;
    }
}
