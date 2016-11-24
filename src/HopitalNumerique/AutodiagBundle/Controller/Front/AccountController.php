<?php

namespace HopitalNumerique\AutodiagBundle\Controller\Front;

use HopitalNumerique\AutodiagBundle\Entity\Synthesis;
use HopitalNumerique\AutodiagBundle\Form\Type\Synthesis\CompareType;
use HopitalNumerique\AutodiagBundle\Model\Synthesis\CompareCommand;
use HopitalNumerique\AutodiagBundle\Service\Synthesis\SynthesisGenerator;
use HopitalNumerique\AutodiagBundle\Service\Synthesis\SynthesisRemover;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\AutodiagBundle\Service\Synthesis\DataFormatter;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AccountController extends Controller
{
    public function indexAction(Request $request, Domaine $domain = null)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $domainesUser = $currentUser->getDomaines();

        // Si l'utilisateur choisit un domaine, on vérifie qu'il y a accès
        if ($domain != null) {
            $found = false;
            foreach ($domainesUser as $domaineUser) {
                if ($domaineUser == $domain) {
                    $found = true;
                    break;
                }
            }

            if ($found == false) {
                throw new HttpException(403);
            }
        }

        $comparisonForm = $this->createForm(CompareType::class, new CompareCommand(), [
            'user' => $this->getUser(),
            'domaine' => $domain,
        ]);

        $dataFormatter = $this->get('autodiag.synthesis.dataformatter');
        $datasForSyntheses = $dataFormatter->getSynthesesOrderByAutodiag($currentUser, $domain);

        if ($request->isXmlHttpRequest()) {
            return $this->render('HopitalNumeriqueAutodiagBundle:Account/partials:autodiag_list.html.twig', array(
                'datasForSyntheses' => $datasForSyntheses,
                'user' => $currentUser,
            ));
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:Account:index.html.twig', array(
            'datasForSyntheses' => $datasForSyntheses,
            'domainesUser' => $domainesUser,
            'user' => $currentUser,
            'currentDomain' => $domain,
            'comparisonForm' => $comparisonForm->createView(),
        ));
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function generateSynthesisAction(Request $request)
    {
        $form = $request->request;

        if (count($form->get('synthesis-choice')) > 1) {
            $this->addFlash('danger', $this->get('translator')->trans('ad.synthesis.generator.error.more_than_one_ad'));

            return $this->redirect($request->headers->get('referer'));
        }

        if ($form->get('synthesis-name') == "") {
            $this->addFlash('danger', $this->get('translator')->trans('ad.synthesis.generator.error.empty_name'));

            return $this->redirect($request->headers->get('referer'));
        }

        $autodiagId = key($form->get('synthesis-choice'));
        $autodiag = $this->getDoctrine()->getRepository('HopitalNumeriqueAutodiagBundle:Autodiag')->findOneBy([
            'id' => $autodiagId,
        ]);

        $synthesisRepository = $this->getDoctrine()->getRepository('HopitalNumeriqueAutodiagBundle:Synthesis');
        $synthesesId = $form->get('synthesis-choice')[$autodiagId];
        $syntheses = [];

        foreach ($synthesesId as $id) {
            $synth = $synthesisRepository->findOneBy(array('id' => $id));
            if (!is_null($synth) && $this->isGranted('read', $synth)) {
                $syntheses[] = $synth;
            }
        }

        try {
            $newSynthesis = $this
                ->get('autodiag.synthesis.generator')
                ->generateSynthesis($autodiag, $syntheses, $this->getUser())
            ;
        } catch (Exception $e) {
            if ($e->getCode() == SynthesisGenerator::SYNTHESIS_NOT_ALLOWED) {
                $this->addFlash('danger', $this->get('translator')->trans('ad.synthesis.generator.error.not_allowed'));
            } elseif ($e->getCode() == SynthesisGenerator::NEED_AT_LEAST_2) {
                $this->addFlash('danger', $this->get('translator')->trans('ad.synthesis.generator.error.at_least_2'));
            } else {
                $this->addFlash('danger', $this->get('translator')->trans('ad.synthesis.generator.error.general'));
            }

            return $this->redirect($request->headers->get('referer'));
        }

        if (isset($newSynthesis)) {
            $newSynthesis->setName($form->get('synthesis-name'));
            $this->getDoctrine()->getManager()->persist($newSynthesis);
            $this->getDoctrine()->getManager()->flush();

            $this->get('autodiag.score_calculator')->deferSynthesisScore($newSynthesis);
        }

        $this->addFlash('success', $this->get('translator')->trans('ad.synthesis.generator.success'));

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @param Request $request
     * @param Synthesis $synthesis
     * @param int $currentSynthesisId Id de la synthèse si suppression effectuée depuis la page de partage
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Synthesis $synthesis, $currentSynthesisId = null)
    {
        $user = $this->getUser();
        $synthesisId = $synthesis->getId();

        if (!$this->isGranted('delete', $synthesis)) {
            $this->addFlash('danger', $this->get('translator')->trans('ad.synthesis.delete.forbidden'));

            return $this->redirect($request->headers->get('referer'));
        }

        if ($synthesis->getUser() === $this->getUser()) {
            $removeState = $this->get('autodiag.synthesis.remover')->removeSynthesis($synthesis, $user);
        } else {
            $removeState = $this->get('autodiag.synthesis.remover')->removeShare($synthesis, $user);
        }

        if ($removeState == SynthesisRemover::SYNTHESIS_REMOVED) {
            $this->addFlash('success', $this->get('translator')->trans('ad.synthesis.delete.succes'));
        } elseif ($removeState == SynthesisRemover::SHARE_REMOVED) {
            $this->addFlash('success', $this->get('translator')->trans('ad.synthesis.share.delete'));
        } else {
            $this->addFlash('danger', $this->get('translator')->trans('ad.synthesis.delete.error'));
        }

        // Si on supprime la synthèse courante depuis la page de partage
        // on redirige vers la page mon compte pour éviter les 404
        if ($currentSynthesisId == $synthesisId) {
            return $this->redirectToRoute('hopitalnumerique_autodiag_account_index');
        }

        return $this->redirect($request->headers->get('referer'));
    }

}
