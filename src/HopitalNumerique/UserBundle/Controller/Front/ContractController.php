<?php

namespace HopitalNumerique\UserBundle\Controller\Front;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\UserBundle\Entity\Contractualisation;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
     * @return BinaryFileResponse|RedirectResponse
     */
    public function downloadAction(Contractualisation $contract)
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists($contract->getAbsolutePath())) {
            $this->addFlash('danger', $this->get('translator')->trans('download.error.file_not_found', [], 'contract'));

            return new RedirectResponse($this->generateUrl('account_profile').'#?contract');
        }

        return new BinaryFileResponse($contract->getAbsolutePath());
    }
}
