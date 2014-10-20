<?php

namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class StatClicController extends Controller
{
    /**
     * Ajout d'un clic en AJAX.
     *
     * @param Request $request
     */
    public function addAction(Request $request)
    {
        $infraDoc = null;

        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();

        //Ne pas prendre en compte l'admin
        if($this->container->get('security.context')->isGranted('ROLE_ADMINISTRATEUR_1'))
        {
            return new Response('{"success":true}', 200);
        }

        $idExpBesoinReponses = $request->request->get('id');

        $expBesoinReponses = $this->get('hopitalnumerique_recherche.manager.expbesoinreponses')->findOneBy(array('id' => $idExpBesoinReponses));

        $statClic = $this->get('hopitalnumerique_recherche.manager.statClic')->createEmpty();

        if(!is_null($user) && $user !== "anon.")
            $statClic->setUser($user);

        $statClic->setReponse($expBesoinReponses);
        $statClic->setDateClic(new \DateTime());

        $this->get('hopitalnumerique_recherche.manager.statClic')->save($statClic);

        return new Response('{"success":true}', 200);
    }
}