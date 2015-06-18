<?php

namespace HopitalNumerique\ExpertBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use HopitalNumerique\ExpertBundle\Entity\ActiviteExpert;

class FrontController extends Controller
{
    /**
     * [listAction description]
     *
     * @param ActiviteExpert $activiteExpert [description]
     *
     * @return [type]
     */
    public function indexAction()
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();

        if($this->get('security.context')->isGranted('ROLE_EXPERT_6'))
        {
            $activites = $this->get('hopitalnumerique_expert.manager.activiteexpert')->getActivitesForExpert($user->getId());
        }
        elseif($this->get('security.context')->isGranted('ROLE_ADMINISTRATEUR_1') || $this->get('security.context')->isGranted('ROLE_ANAP_MEMBRES_2'))
        {
            $activites = $this->get('hopitalnumerique_expert.manager.activiteexpert')->getActivitesForAnapien($user->getId());
        }
        else
        {
            $activites = array();
        }

        $montantVacation = intval($this->get('hopitalnumerique_reference.manager.reference')->findOneById(560)->getLibelle());

        return $this->render('HopitalNumeriqueExpertBundle:Front:index.html.twig', array(
            'activites'       => $activites,
            'montantVacation' => $montantVacation
        ));
    }
}
