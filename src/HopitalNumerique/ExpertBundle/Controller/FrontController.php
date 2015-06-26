<?php

namespace HopitalNumerique\ExpertBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use HopitalNumerique\ExpertBundle\Entity\ActiviteExpert;
use HopitalNumerique\UserBundle\Entity\User;

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
    
    public function biographieAction(User $user)
    {
        return $this->render( 'HopitalNumeriqueExpertBundle:Front:fancy.html.twig' , array(
            'user' => $user
        ));
    }

    /**
     * [expertAction description]
     *
     * @return [type]
     */
    public function expertAction()
    {
        $users   = $this->get('hopitalnumerique_user.manager.user')->findUsersByRole('ROLE_EXPERT_6');
        $experts = array();

        $images = array(
            '1329' => '',
            '254' => '',
            '1335' => '',
            '1328' => '',
            '1333' => '',
            '1325' => '',
            '1188' => '',
            '1326' => '',
            '1114' => '',
            '1572' => '',
            '1334' => '',
            '1090' => '',
            '1331' => '',
            '1025' => ''
        );

        foreach ($users as $user) 
        {
            if(array_key_exists($user->getId(), $images))
            {
                $images[$user->getId()] = $user->getWebPath(); 
                $experts[$user->getId()] = $user->getAppellation(); 
            }
        }

        $images = $this->shuffle_assoc($images);

        return $this->render('HopitalNumeriqueExpertBundle:Front:expert.html.twig', array(
            'mosaiques' => $images,
            'experts'   => $experts
        ));
    }

    private function shuffle_assoc($list) { 
      if (!is_array($list)) return $list; 

      $keys = array_keys($list); 
      shuffle($keys); 
      $random = array(); 
      foreach ($keys as $key) { 
        $random[$key] = $list[$key]; 
      }
      return $random; 
    } 
}
