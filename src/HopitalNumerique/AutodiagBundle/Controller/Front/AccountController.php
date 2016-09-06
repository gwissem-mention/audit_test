<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Service\Synthesis\SynthesisGenerator;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\AutodiagBundle\Service\Synthesis\DataFormer;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class AccountController extends Controller
{
    public function indexAction()
    {

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $domainesUser = $currentUser->getDomaines();

        $dataFormer = $this->get('autodiag.synthesis.dataformer');
        $datasForSyntheses = $dataFormer->getSynthesesByAutodiag($currentUser);

        return $this->render('HopitalNumeriqueAutodiagBundle:Account:index.html.twig', array(
            'datasForSyntheses' => $datasForSyntheses,
            'domainesUser' => $domainesUser,
            'user' => $currentUser,
        ));
    }

    public function generateSynthesisAction(Request $request)
    {
        $form = $request->request;

        if (count($form->get('synthesis-choice')) > 1) {
            $this->addFlash('danger', $this->get('translator')->trans('ad.synthesis.generator.error.more_than_one_ad'));

            return $this->redirectToRoute('hopitalnumerique_autodiag_account_index');
        }

        $autodiagId = key($form->get('synthesis-choice'));
        $autodiag = $this->getDoctrine()->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag')->findOneBy([
                'id' => $autodiagId,
        ]);

        $synthesisRepository = $this->getDoctrine()->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis');
        $synthesesId = $form->get('synthesis-choice')[$autodiagId];
        $syntheses = [];

        foreach ($synthesesId as $id) {
            $syntheses[] = $synthesisRepository->findOneBy(array('id' => $id));
        }

        try {
            $this->get('autodiag.synthesis.generator')->generateSynthesis($autodiag, $syntheses);
        } catch (Exception $e) {
            if ($e->getCode() == SynthesisGenerator::SYNTHESIS_NOT_ALLOWED) {
                $this->addFlash('danger', $this->get('translator')->trans('ad.synthesis.generator.error.not_allowed'));
            } elseif ($e->getCode() == SynthesisGenerator::NEED_AT_LEAST_2) {
                $this->addFlash('danger', $this->get('translator')->trans('ad.synthesis.generator.error.at_least_2'));
            }

            return $this->redirectToRoute('hopitalnumerique_autodiag_account_index');
        }

        $this->addFlash(
            'success',
            $this->get('translator')->trans('ad.synthesis.generator.success')
        );

        return $this->redirectToRoute('hopitalnumerique_autodiag_account_index');
    }
}

