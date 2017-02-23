<?php

namespace HopitalNumerique\AdminBundle\Service;

use HopitalNumerique\InterventionBundle\Repository\InterventionDemandeRepository;
use HopitalNumerique\ModuleBundle\Repository\InscriptionRepository;
use HopitalNumerique\PaiementBundle\Repository\FactureRepository;

class PaymentGridBlock
{
    /** @var FactureRepository $factureRepository */
    private $factureRepository;
    /** @var InscriptionRepository $inscriptionRepository */
    private $inscriptionRepository;
    /** @var InterventionDemandeRepository $interventionDemandeRepository */
    private $interventionDemandeRepository;

    /**
     * PaymentGridBlock constructor.
     *
     * @param FactureRepository             $factureRepository
     * @param InscriptionRepository         $inscriptionRepository
     * @param InterventionDemandeRepository $interventionDemandeRepository
     */
    public function __construct(
        FactureRepository $factureRepository,
        InscriptionRepository $inscriptionRepository,
        InterventionDemandeRepository $interventionDemandeRepository
    ) {
        $this->factureRepository             = $factureRepository;
        $this->inscriptionRepository         = $inscriptionRepository;
        $this->interventionDemandeRepository = $interventionDemandeRepository;
    }

    public function getBlockDatas()
    {
        $paymentsDatas = [
            'payedPreviousYear'          => $this->factureRepository->getTotalAmountForYear(date('Y') - 1),
            'payedCurrentYear'           => $this->factureRepository->getTotalAmountForYear(date('Y')),
            'waitingPayment'             => $this->factureRepository->getTotalNotPayedAmountForYear(),
            'waintingPaymentCurrentYear' => $this->factureRepository->getTotalNotPayedAmountForYear(date('Y')),
            'waitingBillCreation'        => $this->inscriptionRepository->getAmountOfSessionWithoutBill() +
                                            $this->interventionDemandeRepository->getAmountOfInterventionWithoutBill(),
        ];

        return $paymentsDatas;
    }
}
