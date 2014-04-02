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
        $context = $this->container->get('router')->getContext();
        $urlSite = $context->getScheme() . '://' . $context->getHost().$context->getBaseUrl();

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

                    //prepare somes vars
                    $requeteNew     = false;
                    $requeteUpdated = false;
                    $news           = '';
                    $updateds       = '';

                    //handles objets
                    foreach($objets as $objet)
                    {
                        //si l'objet est nouveau : la requete doit etre taggué nouvelle
                        if( $objet['new'] === true && !$requeteNew )
                        {
                            $requeteNew = true;
                            
                            if( !is_null($objet['objet']) )
                                $url = $this->generateUrl('hopital_numerique_publication_publication_contenu', array('id'=>$objet['objet'], 'alias'=>$objet['aliasO'], 'idc'=>$objet['id'], 'aliasc'=>$objet['aliasC']) );
                            else
                                $url = $this->generateUrl('hopital_numerique_publication_publication_contenu', array('id'=>$objet['id'], 'alias'=>$objet['alias']) );

                            $news .= '<li><a target="_blank" href="'.$urlSite.$url.'" >'.ucfirst($objet['titre']).'</a></li>';
                        }

                        //si l'objet est mis à jour : la requete doit etre taggué mise à jour
                        if( $objet['updated'] === true && !$requeteUpdated )
                        {
                            $requeteUpdated = true;

                            if( !is_null($objet['objet']) )
                                $url = $this->generateUrl('hopital_numerique_publication_publication_contenu', array('id'=>$objet['objet'], 'alias'=>$objet['aliasO'], 'idc'=>$objet['id'], 'aliasc'=>$objet['aliasC']) );
                            else
                                $url = $this->generateUrl('hopital_numerique_publication_publication_contenu', array('id'=>$objet['id'], 'alias'=>$objet['alias']) );

                            $updateds .= '<li><a target="_blank" href="'.$urlSite.$url.'" >'.ucfirst($objet['titre']).'</a></li>';
                        }
                    }

                    //update Requete entity
                    $requete->setNew( $requeteNew );
                    $requete->setUpdated( $requeteUpdated );

                    //check if User has to be notified
                    if( $requete->isUserNotified() ) {
                        $today = new \DateTime();

                        if( $requete->getDateDebut() < $today && $today < $requete->getDateFin() )
                        {
                            //format listes and build Options
                            $options                           = array();
                            $options['nouvellespublications']  = count($news) > 0     ? '<ul>'.$news.'</ul>'     : '<ul><li> - Aucune nouvelle publication - </li></ul>';
                            $options['misesajourpublications'] = count($updateds) > 0 ? '<ul>'.$updateds.'</ul>' : '<ul><li> - Aucune publication mise à jour - </li></ul>';
                            $options['requete']                = ucfirst($requete->getNom());

                            //send mail
                            $mail = $this->get('nodevo_mail.manager.mail')->sendNotificationRequete($user, $options );
                            $this->get('mailer')->send($mail);
                            $this->get('hopitalnumerique_recherche.service.logger.cronlogger')->addLog('mail send to : ' . $user->getEmail() );
                        }
                    }
                }

                //save
                $this->get('hopitalnumerique_recherche.manager.requete')->save( $requetes );
            }

            return new Response($this->get('hopitalnumerique_recherche.service.logger.cronlogger')->getHtml().'<p>Fin du traitement : OK.</p>');
        }
        
        return new Response('Clef invalide.');
    }
}