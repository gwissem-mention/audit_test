<?php

namespace HopitalNumerique\FaqBundle\Controller;

use Nodevo\FaqBundle\Controller\FaqController as NodevoController;

class FaqController extends NodevoController
{
    public function faqAction()
    {
        $elements = $this->get('nodevo_faq.manager.faq')->findAll();

        return $this->render('HopitalNumeriqueFaqBundle:Faq:faq.html.twig', array(
            'elements' => $elements
        ));
    }
}
