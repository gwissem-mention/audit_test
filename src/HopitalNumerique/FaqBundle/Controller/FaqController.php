<?php

namespace HopitalNumerique\FaqBundle\Controller;

use Nodevo\FaqBundle\Controller\FaqController as NodevoController;

class FaqController extends NodevoController
{
    public function faqAction()
    {
        $domaineId = $this->get('request')->getSession()->get('domaineId');

        $elements = $this->get('nodevo_faq.manager.faq')->getFaqByDomaine($domaineId);

        return $this->render('HopitalNumeriqueFaqBundle:Faq:faq.html.twig', array(
            'elements' => $elements
        ));
    }
}
