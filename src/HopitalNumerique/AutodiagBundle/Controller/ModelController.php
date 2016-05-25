<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Domain\ModelAdminUpdate;
use HopitalNumerique\AutodiagBundle\Entity\Model;
use HopitalNumerique\AutodiagBundle\Form\Type\Domain\ModelAdminUpdateType;
use HopitalNumerique\AutodiagBundle\Grid\ModelGrid;
use HopitalNumerique\AutodiagBundle\Service\Model\ModelFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ModelController extends Controller
{
    /**
     * Grid that list autodiag Models
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $grid = new ModelGrid($this->container);

        return $grid->render('HopitalNumeriqueAutodiagBundle:Model:list.html.twig');
    }

    public function createAction(Request $request)
    {
        $modelFactory = new ModelFactory();
        $model = $modelFactory->create();

        return $this->editAction($request, $model);
    }

    /**
     * Edit autodiag model
     *
     * @param Request $request
     * @param Model $model
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Model $model)
    {
        $domain = new ModelAdminUpdate();
        $domain->setModel($model);
        $criticite = $this->get('autodiag.attribute_builder_provider')->getBuilder('criticite')->getPreset($model) ?: new Model\Preset($model, 'criticite');
        $maitrise = $this->get('autodiag.attribute_builder_provider')->getBuilder('maitrise')->getPreset($model) ?: new Model\Preset($model, 'maitrise');
        $domain->addPreset($criticite);
        $domain->addPreset($maitrise);

        $form = $this->createForm(new ModelAdminUpdateType($this->get('autodiag.attribute_builder_provider')), $domain);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($domain->getModel());
            $manager->flush();

            foreach ($domain->getPresets() as $preset) {
                $this->get('autodiag.attribute_builder_provider')
                    ->getBuilder($preset->getType())
                    ->setPreset($domain->getModel(), $preset->getPreset());
            }

            $this->addFlash('success', 'Autodiag enregistré');
            return $this->redirectToRoute('hopitalnumerique_autodiag_model_edit', [
                'id' => $model->getId()
            ]);
        }

        return $this->render('HopitalNumeriqueAutodiagBundle:Model:edit.html.twig', [
            'form' => $form->createView(),
            'model' => $model,
        ]);
    }
}
