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

        $totalVacation      = array();
        $evenementVacations = array();

        if($this->get('security.context')->isGranted('ROLE_EXPERT_6'))
        {
            $activites = $this->get('hopitalnumerique_expert.manager.activiteexpert')->getActivitesForExpert($user->getId());

            //Calcul des vacations des différentes activité en fonctions de la présence ou non des différents experts
            foreach ($activites as $activite) 
            {
                $totalVacation[$activite->getId()] = 0;

                foreach ($activite->getEvenements() as $evenement) 
                {
                    foreach ($evenement->getExperts() as $expert) 
                    {
                        //Récupération des données de l'expert courant uniquement
                        if($expert->getExpertConcerne()->getId() !== $user->getId())
                        {
                            continue;
                        } 

                        if(!array_key_exists($evenement->getId(), $evenementVacations))
                        {
                            $evenementVacations[$evenement->getId()] = 0;
                        }

                        if($expert->getPresent())
                        {
                            $evenementVacations[$evenement->getId()]++;
                            $totalVacation[$activite->getId()] += $evenement->getNbVacation();
                        }
                    }
                }
            }
        }
        elseif($this->get('security.context')->isGranted('ROLE_ADMINISTRATEUR_1') || $this->get('security.context')->isGranted('ROLE_ANAP_MEMBRES_2'))
        {
            $activites = $this->get('hopitalnumerique_expert.manager.activiteexpert')->getActivitesForAnapien($user->getId());

            //Calcul des vacations des différentes activité en fonctions de la présence ou non des différents experts
            foreach ($activites as $activite) 
            {
                $totalVacation[$activite->getId()] = 0;

                foreach ($activite->getEvenements() as $evenement) 
                {
                    foreach ($evenement->getExperts() as $expert) 
                    {
                        if(!array_key_exists($evenement->getId(), $evenementVacations))
                        {
                            $evenementVacations[$evenement->getId()] = 0;
                        }

                        if($expert->getPresent())
                        {
                            $evenementVacations[$evenement->getId()]++;
                            $totalVacation[$activite->getId()] += $evenement->getNbVacation();
                        }
                    }
                }
            }
        }
        else
        {
            $activites = array();
        }

        $montantVacation = intval($this->get('hopitalnumerique_reference.manager.reference')->findOneById(560)->getLibelle());

        return $this->render('HopitalNumeriqueExpertBundle:Front:index.html.twig', array(
            'activites'          => $activites,
            'totalVacation'      => $totalVacation,
            'montantVacation'    => $montantVacation,
            'evenementVacations' => $evenementVacations
        ));
    }
}
