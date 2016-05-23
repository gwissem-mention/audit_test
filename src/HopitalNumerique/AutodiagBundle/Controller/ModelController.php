<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Model;
use HopitalNumerique\AutodiagBundle\Form\Type\ModelType;
use HopitalNumerique\AutodiagBundle\Grid\ModelGrid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

    /**
     * Edit autodiag model
     * @param Model $model
     */
    public function editAction(Model $model)
    {
        $form = $this->createForm(ModelType::class, $model);

        return $this->render('HopitalNumeriqueAutodiagBundle:Model:edit.html.twig', [
            'form' => $form->createView(),
            'model' => $model,
        ]);
    }
}
