<?php

namespace HopitalNumerique\ExpertBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use HopitalNumerique\ExpertBundle\Entity\ActiviteExpert;
use HopitalNumerique\ExpertBundle\Entity\DateFictiveActiviteExpert;

class DateFictiveController extends Controller
{
    /**
     * Création d'une date fictive
     *
     * @param Request        $request        [description]
     * @param ActiviteExpert $activiteExpert [description]
     */
    public function addAction(Request $request, ActiviteExpert $activiteExpert)
    {
        //créer une date fictive
        $dateFictive = $this->get('hopitalnumerique_expert.manager.datefictive')->createEmpty();

        $dateFictive->setDate( new \DateTime($request->request->get('date_fictive')));
        $dateFictive->setActivite($activiteExpert);

        //save
        $this->get('hopitalnumerique_expert.manager.datefictive')->save( $dateFictive );

        return $this->redirect( $this->generateUrl('hopitalnumerique_expert_expert_liste_date_fictive', array( 'id' => $activiteExpert->getId() ) ) );
    }

    public function deleteAction(DateFictiveActiviteExpert $dateFictive)
    {
        $activiteExpert = $dateFictive->getActivite();

        $this->get('hopitalnumerique_expert.manager.datefictive')->delete( $dateFictive );
        
        return $this->redirect( $this->generateUrl('hopitalnumerique_expert_expert_activite_edit', array( 'id' => $activiteExpert->getId() ) ) );
    }

    public function listAction(ActiviteExpert $activiteExpert)
    {
        $dateFictives = $this->get('hopitalnumerique_expert.manager.datefictive')->findBy(array('activite' => $activiteExpert), array('date' => 'ASC'));

        return $this->render('HopitalNumeriqueExpertBundle:DateFictive:list.html.twig', array(
            'dateFictives' => $dateFictives
        ));
    }
}
