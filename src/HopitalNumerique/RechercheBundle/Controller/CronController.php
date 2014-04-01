<?php
namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CronController extends Controller
{
    /**
     * Cron de mise à jour des requetes
     */
    public function requetesAction($id)
    {
        if ($id == 'FHFURJYIHOLPMFKVIDUESQGEUDRCTUFT')
        {
            $users = $this->get('hopitalnumerique_user.manager.user')->findAll();
            foreach( $users as $user )
            {
                //get rôle
                $role = $this->get('nodevo_role.manager.role')->getUserRole( $user );

                //get requetes for the user
                $requetes = $this->get('hopitalnumerique_recherche.manager.requete')->findBy( array('user'=>$user) );

                //handle each requete
                foreach($requetes as &$requete)
                {
                    //get objets and format them
                    $objets = $this->get('hopitalnumerique_recherche.manager.search')->getObjetsForRecherche( $requete->getRefs(), $role );
                    $objets = $this->get('hopitalnumerique_objet.manager.consultation')->updateObjetsWithConnectedUser( $objets, $user );

                    //handles objets
                    $requeteNew     = false;
                    $requeteUpdated = false;
                    foreach($objets as $objet)
                    {
                        //si l'objet est nouveau : la requete doit etre taggué nouvelle
                        if( $objet['new'] === true && !$requeteNew )
                            $requeteNew = true;

                        //si l'objet est mis à jour : la requete doit etre taggué mise à jour
                        if( $objet['updated'] === true && !$requeteUpdated )
                            $requeteUpdated = true;
                            
                        //si la requete est à la fois nouvelle ET mise à jour, on passe à la requete suivante
                        if( $requeteNew && $requeteUpdated )
                            break;
                    }

                    //update Requete entity
                    $requete->setNew( $requeteNew );
                    $requete->setUpdated( $requeteUpdated );

                    //check if User has to be notified
                    if( $requete->isUserNotified() ) {
                        $today = new \DateTime();
                        
                        if( $requete->getDateDebut() > $today && $today < $requete->getDateFin() )
                        {
                            $mail = $this->get('nodevo_mail.manager.mail')->sendNotificationRequete($user, array('requete'=>$requete->getNom()) );
                            $this->get('mailer')->send($mail);
                        }
                    }
                }

                //save
                $this->get('hopitalnumerique_recherche.manager.requete')->save( $requetes );
            }            
        }
        
        return new Response();
    }
}