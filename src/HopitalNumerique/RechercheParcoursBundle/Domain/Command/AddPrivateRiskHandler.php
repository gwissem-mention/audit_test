<?php

namespace HopitalNumerique\RechercheParcoursBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use HopitalNumerique\ObjetBundle\Repository\RiskRepository;
use HopitalNumerique\ReferenceBundle\Entity\Reference;

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
     * AddPrivateRiskHandler constructor.
     *
     * @param RiskRepository $riskRepository
     * @param EntityManagerInterface $entityManagerInterface
     * @param CurrentDomaine $currentDomain
     */
    public function __construct(RiskRepository $riskRepository, EntityManagerInterface $entityManager, CurrentDomaine $currentDomain)
    {
        $this->riskRepository = $riskRepository;
        $this->entityManager = $entityManager;
        $this->currentDomain = $currentDomain;
    }

    /**
     * @param AddPrivateRiskCommand $addPrivateRiskCommand
     */
    public function handle(AddPrivateRiskCommand $addPrivateRiskCommand)
    {
        $risk = $this->getOrCreateRisk($addPrivateRiskCommand);

        $addPrivateRiskCommand->guidedSearch->addPrivateRisk($risk);

        if (!is_null($addPrivateRiskCommand->user)) {
            $risk->addOwner($addPrivateRiskCommand->user);
        }

        $this->entityManager->flush();
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
