<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\ObjetBundle\Entity\RelatedRisk;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use HopitalNumerique\ObjetBundle\Repository\RiskRepository;
use HopitalNumerique\ObjetBundle\Service\Risk\Fusion;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EditRiskHandler
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
     * @var Fusion $riskFusion
     */
    protected $riskFusion;

    /**
     * EditRiskCommand constructor.
     *
     * @param RiskRepository $riskRepository
     * @param EntityManagerInterface $entityManager
     * @param Fusion $riskFusion
     */
    public function __construct(RiskRepository $riskRepository, EntityManagerInterface $entityManager, Fusion $riskFusion)
    {
        $this->riskRepository = $riskRepository;
        $this->entityManager = $entityManager;
        $this->riskFusion = $riskFusion;
    }

    /**
     * @param EditRiskCommand $editRiskCommand
     *
     * @return Risk
     */
    public function handle(EditRiskCommand $editRiskCommand)
    {
        if (is_null($editRiskCommand->riskId)) {
            $risk = new Risk();

            $this->entityManager->persist($risk);
        } else {
            $risk = $this->riskRepository->find($editRiskCommand->riskId);
        }

        $risk
            ->setLabel($editRiskCommand->label)
            ->setNature($editRiskCommand->nature)
            ->setDomains($editRiskCommand->domains)
            ->setArchived($editRiskCommand->archived)
            ->setPrivate(!$editRiskCommand->publish)
        ;

        if ($editRiskCommand->confirmFusion && !is_null($editRiskCommand->fusionTarget)) {
            $risk = $this->riskFusion->fusion($risk, $editRiskCommand->fusionTarget);
        }

        $this->entityManager->flush();

        return $risk;
    }
}
