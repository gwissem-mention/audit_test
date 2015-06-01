<?php
namespace HopitalNumerique\RechercheBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CronController extends Controller
{
    /**
     * Cron de mise à jour des requetes
     */
    public function requetesAction(Request $request, $id)
    {
        if ($id == 'FHFURJYIHOLPMFKVIDUESQGEUDRCTUFT')
        {
            ini_set('max_execution_time', 0);

            $domaineId = $request->getSession()->get('domaineId');

            $context = $this->container->get('router')->getContext();
            $urlSite = $context->getScheme() . '://' . $context->getHost().$context->getBaseUrl();
            $today   = new \DateTime();

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
                    //Récupération des catégories filtrées
                    $categs       = $requete->getCategPointDur();
                    $arrayCategId = explode(',', $categs);
                    $arrayCateg   = array();
                    foreach ($arrayCategId as $id) 
                    {
                        if(trim($id))
                        {
                            $arrayCateg[] = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy(array('id' => $id))->getLibelle();
                        }
                    }

                    //get objets and format them
                    $refsPonderees = $this->get('hopitalnumerique_reference.manager.reference')->getReferencesPonderees();
                    $objets = $this->get('hopitalnumerique_recherche.manager.search')->getObjetsForRecherche( $requete->getRefs(), $role, $refsPonderees );
                    $objets = $this->get('hopitalnumerique_objet.manager.consultation')->updateObjetsWithConnectedUser( $domaineId, $objets, $user );

                    //prepare somes vars
                    $requeteNew     = false;
                    $requeteUpdated = false;
                    $news           = '';
                    $updateds       = '';

                    //handles objets
                    foreach($objets as $objet)
                    {
                        if(!empty($arrayCateg))
                        {
                            //Uniquement sur les prodcution
                            if($objet["categ"] === "production")
                            {
                                //Récupèration de tout les types de l'objet
                                $types = explode('♦', $objet["type"]);
                                $isInArray = false;
                                foreach ($types as $type) 
                                {
                                    if(in_array(trim($type), $arrayCateg))
                                    {
                                        $isInArray = true;
                                        break;
                                    }
                                }
                                //Si la categ n'est pas dans le tableau des types de l'objets, on ne le prend pas en compte
                                if(!$isInArray)
                                {
                                    continue;
                                }
                            }
                        }
                        //si l'objet est nouveau : la requete doit etre taggué nouvelle
                        if( (isset($objet['new']) && $objet['new'] === true && !$requeteNew) )//&& $objet['created']->modify('+ 1 day')->format('d-m-Y') == $today->format('d-m-Y') )
                        {
                            $requeteNew = true;
                            
                            if( !is_null($objet['objet']) )
                                $url = $this->generateUrl('hopital_numerique_publication_publication_contenu', array('id'=>$objet['objet'], 'alias'=>$objet['aliasO'], 'idc'=>$objet['id'], 'aliasc'=>$objet['aliasC']) );
                            else
                                $url = $this->generateUrl('hopital_numerique_publication_publication_objet', array('id'=>$objet['id'], 'alias'=>$objet['alias']) );

                            $news .= '<li><a target="_blank" href="'.$urlSite.$url.'" >'.ucfirst($objet['titre']).'</a></li>';
                        }

                        //si l'objet est mis à jour : la requete doit etre taggué mise à jour
                        if( (isset( $objet['updated'] ) && $objet['updated'] === true ))//&& !$requeteUpdated) && $objet['modified']->modify('+ 1 day')->format('d-m-Y') == $today->format('d-m-Y') )
                        {
                            $requeteUpdated = true;

                            if( !is_null($objet['objet']) )
                                $url = $this->generateUrl('hopital_numerique_publication_publication_contenu', array('id'=>$objet['objet'], 'alias'=>$objet['aliasO'], 'idc'=>$objet['id'], 'aliasc'=>$objet['aliasC']) );
                            else
                                $url = $this->generateUrl('hopital_numerique_publication_publication_objet', array('id'=>$objet['id'], 'alias'=>$objet['alias']) );

                            $updateds .= '<li><a target="_blank" href="'.$urlSite.$url.'" >'.ucfirst($objet['titre']).'</a></li>';
                        }
                    }

                    //update Requete entity
                    $requete->setNew( $requeteNew );
                    $requete->setUpdated( $requeteUpdated );

                    //check if User has to be notified
                    if( $requete->isUserNotified() && ($requeteNew || $requeteUpdated) ) {
                        $today = new \DateTime();

                        if( ( is_null($requete->getDateDebut()) || $requete->getDateDebut() < $today ) && (is_null($requete->getDateFin()) || $today < $requete->getDateFin()) )
                        {
                            //format listes and build Options
                            $options                           = array();
                            $options['nouvellespublications']  = $requeteNew     ? '<ul>'.$news.'</ul>'     : '<ul><li> - Aucune nouvelle publication - </li></ul>';
                            $options['misesajourpublications'] = $requeteUpdated ? '<ul>'.$updateds.'</ul>' : '<ul><li> - Aucune publication mise à jour - </li></ul>';
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