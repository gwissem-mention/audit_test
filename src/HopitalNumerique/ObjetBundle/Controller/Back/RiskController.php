<?php

namespace HopitalNumerique\ObjetBundle\Controller\Back;

use Doctrine\ORM\ORMException;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Domain\Command\DeleteRiskCommand;
use HopitalNumerique\ObjetBundle\Domain\Command\EditRiskCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\ObjetBundle\Entity\Risk;
use HopitalNumerique\ObjetBundle\Form\RiskType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class RiskController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $grid = $this->get('hopitalnumerique_objet.grid.risk');

        return $grid->render('HopitalNumeriqueObjetBundle:back:risk\list.html.twig');
    }

    /**
     * @param Request $request
     * @param Risk|null $risk
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Risk $risk = null)
    {
        $riskCommand = EditRiskCommand::createFromRisk($this->getUser(), $risk);;

        $form = $this->createForm(RiskType::class, $riskCommand);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $risk = $this->get('hopitalnumerique_objet.handler.edit_risk')->handle($riskCommand);

            $this->addFlash('success', $this->get('translator')->trans('edit.notifications.saved', [], 'risk'));

            if ($request->request->get('do') === 'save-close') {
                return $this->redirectToRoute('hopitalnumerique_objet_risk_list');
            } else {
                return $this->redirectToRoute('hopitalnumerique_objet_risk_edit', ['risk' => $risk->getId()]);
            }
        }

        return $this->render('HopitalNumeriqueObjetBundle:back:risk\edit.html.twig', [
            'form' => $form->createView(),
            'risk' => $risk,
            'domainesCommunsWithUser' => $risk ? $this
                ->get('hopitalnumerique_core.dependency_injection.entity')
                ->getEntityDomainesCommunsWithUser($risk, $this->getUser())
                : null
            ,
        ]);
    }

    /**
     * @param Risk $risk
     *
     * @return JsonResponse
     */
    public function deleteAction(Risk $risk)
    {
        if ($success = $this->get('hopitalnumerique_objet.handler.delete_risk')->handle(new DeleteRiskCommand($risk))) {
            $this->addFlash('success', $this->get('translator')->trans('delete.notifications.success', [], 'risk'));
        } else {
            $this->addFlash('danger', $this->get('translator')->trans('delete.notifications.error', [], 'risk'));
        }

        return new JsonResponse([
            'success' => $success,
            'url' => $this->generateUrl('hopitalnumerique_objet_risk_list')
        ]);
    }

    /**
     * @param array $primaryKeys
     * @param array $allPrimaryKeys
     *
     * @return RedirectResponse
     */
    public function deleteMassAction($primaryKeys, $allPrimaryKeys)
    {
        if ($allPrimaryKeys == 1) {
            $risks = $this->get('hopitalnumerique_objet.repository.risk')->findAll();
        } else {
            $risks = $this->get('hopitalnumerique_objet.repository.risk')->findBy(['id' => $primaryKeys]);
        }

        $risksCount = count($risks);
        $riskDeleted = 0;
        foreach ($risks as $risk) {
            $em = $this->getDoctrine()->getManager();
            $risk = $em->getRepository(Risk::class)->find($risk->getId());
            
            try {
                $em->remove($risk);
                $em->flush($risk);
                $riskDeleted++;
            } catch (ORMException $e) {
                $this->getDoctrine()->resetManager();
            }
        }

        if (0 === $riskDeleted) {
            $this->addFlash('danger', $this->get('translator')->trans('massDelete.notifications.error', [], 'risk'));
        } else {
            $this->addFlash('info', $this->get('translator')->trans('massDelete.notifications.success', ['%total%' => $risksCount, '%deleted%' => $riskDeleted], 'risk'));
        }

        return $this->redirect($this->generateUrl('hopitalnumerique_objet_risk_list'));
    }

    /**
     * @param array $primaryKeys
     * @param array $allPrimaryKeys
     *
     * @return Response
     */
    public function exportCsvAction($primaryKeys, $allPrimaryKeys)
    {
        $translator = $this->get('translator');

        if ($allPrimaryKeys == 1) {
            $risks = $this->get('hopitalnumerique_objet.repository.risk')->findAll();
        } else {
            $risks = $this->get('hopitalnumerique_objet.repository.risk')->findById($primaryKeys);
        }

        $colonnes = [
            'id' => $translator->trans('export.columns.id', [], 'risk'),
            'label' => $translator->trans('export.columns.label', [], 'risk'),
            'nature' => $translator->trans('export.columns.nature', [], 'risk'),
            'private' => $translator->trans('export.columns.private', [], 'risk'),
            'archived' => $translator->trans('export.columns.archived', [], 'risk'),
            'domains' => $translator->trans('export.columns.domains', [], 'risk'),
        ];

        $lines = [];
        /** @var Risk $risk */
        foreach ($risks as $risk) {
            $lines[] = [
                'id' => $risk->getId(),
                'label' => $risk->getLabel(),
                'nature' => $risk->getNature(),
                'private' => $translator->trans('export.boolean.'.($risk->isPrivate() ? 'yes' : 'no'), [], 'risk'),
                'archived' => $translator->trans('export.boolean.'.($risk->isArchived() ? 'yes' : 'no'), [], 'risk'),
                'domains' => implode(',', array_map(function (Domaine $domain) {
                    return $domain->getNom();
                    }, $risk->getDomains()->toArray())),
            ];
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_objet.manager.objet')->exportCsv(
            $colonnes,
            $lines,
            'export-risques.csv',
            $kernelCharset
        );
    }
}
