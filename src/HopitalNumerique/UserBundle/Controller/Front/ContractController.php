<?php

namespace HopitalNumerique\UserBundle\Controller\Front;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\UserBundle\Entity\Contractualisation;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Contract controller.
 */
class ContractController extends Controller
{

    /**
     * @param Contractualisation $contract
     *
     * @Security("is_granted('download', contract)")
     *
     * @return BinaryFileResponse
     */
    public function downloadAction(Contractualisation $contract)
    {
        return new BinaryFileResponse($contract->getAbsolutePath());
    }
}
