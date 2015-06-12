<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use HopitalNumerique\AutodiagBundle\Entity\Resultat;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class PartageResultatController extends Controller
{
    /**
     * POP-IN d'envoie du mail
     */
    public function fancyAction(Resultat $resultat)
    {
        return $this->render('HopitalNumeriqueAutodiagBundle:PartageResultat:fancy.html.twig', array(
            'resultat' => $resultat
        ));
    }
    
    /**
     * Envoie de mail 
     */
    public function envoieMailAction(Request $request, Resultat $resultat)
    {
        $success = file_exists(__ROOT_DIRECTORY__ . '/files/autodiag/' . $resultat->getPdf());

        if($success)
        {
            //Récupération des infos de la fancy
            $destinataire = $this->get('request')->request->get('destinataire');
            $sujet        = $this->get('request')->request->get('sujet');
            $message      = $this->get('request')->request->get('message');

            $options = array(
                'destinataire' => $destinataire,
                'sujet'        => $sujet,
                'message'      => $message
            );
            
            //Récupère l'utilsateur connecté
            $user = $this->get('security.context')->getToken()->getUser();
            
            //Envoie du mail de validation de la candidature
            $mail = $this->get('nodevo_mail.manager.mail')->sendPartageResultatAutodiag($options, $resultat);
            $this->get('mailer')->send($mail);
        }


        $response = json_encode(array('success' => ($success ? 'true' : 'false') ));
        
        return new Response($response, 200);
    }
}
