<?php

namespace HopitalNumerique\ObjetBundle\Domain\Command;

use Doctrine\ORM\EntityManagerInterface;
use HopitalNumerique\ObjetBundle\Entity\RelatedRisk;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use HopitalNumerique\ObjetBundle\Repository\RiskRepository;
use HopitalNumerique\ObjetBundle\Service\Risk\Fusion;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DeleteRiskHandler
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
     * EditRiskCommand constructor.
     *
     * @param RiskRepository $riskRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(RiskRepository $riskRepository, EntityManagerInterface $entityManager)
    {
        $this->riskRepository = $riskRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param DeleteRiskCommand $deleteRiskCommand
     *
     * @return boolean
     */
    public function handle(DeleteRiskCommand $deleteRiskCommand)
    {
        try {
            $this->entityManager->remove($deleteRiskCommand->risk);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
