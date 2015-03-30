<?php
namespace HopitalNumerique\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use HopitalNumerique\UserBundle\Entity\User as HopiUser;

/**
 * Controller des abassadeurs
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class AmbassadeurController extends Controller
{
    //---- Front Office ------
    /**
     * Affichage du formulaire d'utilisateur
     * 
     * @param integer $id Identifiant de l'utilisateur
     */
    public function editFrontAction( )
    {
        //On récupère l'utilisateur qui est connecté
        $user = $this->get('security.context')->getToken()->getUser();
        
        //Récupération du questionnaire de l'expert
        $idQuestionnaireAmbassadeur = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('ambassadeur');
        $questionnaire              = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy( array('id' => $idQuestionnaireAmbassadeur) );
        
        //Récupération des réponses pour le questionnaire et utilisateur courant, triées par idQuestion en clé
        $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser( $questionnaire->getId(), $user->getId(), true );

        $themeQuestionnaire = empty($reponses) ? 'vertical' : 'vertical_readonly';
        //readonly si il y a des réponses dans le questionnaire ou que le role courant de l'utilisateur est ambassadeur
        $readOnly = (in_array('ROLE_AMBASSADEUR_7', $user->getRoles()) || !empty($reponses));

        return $this->render('HopitalNumeriqueUserBundle:Ambassadeur/Front:edit.html.twig',array(
            'questionnaire'      => $questionnaire,
            'user'               => $user,
            'optionRenderForm'   => array(
                'showAllQuestions'   => false,
                'readOnly'           => $readOnly,
                'envoieDeMail'       => true,
                'themeQuestionnaire' => $themeQuestionnaire,
                'routeRedirect'      => json_encode(array(
                    'quit' => array(
                        'route'     => 'hopitalnumerique_user_ambassadeur_front_edit',
                        'arguments' => array()
                    )
                ))
            )
        ));
    }
    
    //---- Back Office ------
    /**
     * Affichage du formulaire d'utilisateur
     *
     * @param integer $id Identifiant de l'utilisateur
     */
    public function editAction( HopiUser $user )
    {
        //Récupération du questionnaire de l'expert
        $idQuestionnaireExpert = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('ambassadeur');
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy( array('id' => $idQuestionnaireExpert) );
    
        return $this->render('HopitalNumeriqueUserBundle:Ambassadeur:edit.html.twig',array(
            'questionnaire' => $questionnaire,
                'user'             => $user,
                'options'          => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user),
                'optionRenderForm' => array(
                    'envoieDeMail'  => false,
                    'routeRedirect' => json_encode(array(
                        'quit' => array(
                            'route'     => 'hopital_numerique_user_homepage',
                            'arguments' => array()
                        ),
                        'sauvegarde' => array(
                            'route'     => 'hopitalnumerique_user_ambassadeur_edit',
                            'arguments' => array(
                                'id' => $user->getId()
                            )
                        )
                    ))
                )
        ));
    }

    /**
     * Affichage de la fiche des réponses au questionnaire ambassadeur d'un utilisateur
     *
     * @param integer $idUser          ID de l'utilisateur
     */
    public function showAction( $idUser )
    {
        //Récupération du questionnaire de l'ambassadeur
        $idQuestionnaireAmbassadeur = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('ambassadeur');
    
        //Récupération de l'utilisateur passé en param
        $reponses = $this->get('hopitalnumerique_questionnaire.manager.reponse')->reponsesByQuestionnaireByUser( $idQuestionnaireAmbassadeur , $idUser );
    
        return $this->render('HopitalNumeriqueUserBundle:Ambassadeur:show.html.twig', array(
            'reponses'       => $reponses,
            'nombreReponses' => count($reponses)
        ));
    }
    
    /**
     * Affichage de la liste des objets d'un utilisateur
     *
     * @param integer $idUser          ID de l'utilisateur
     */
    public function listeObjetsAction( $idUser )
    {
        //Récupération de l'utilisateur passé en param
        $objets = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsByAmbassadeur($idUser);
    
        return $this->render('HopitalNumeriqueUserBundle:Ambassadeur:liste_objets.html.twig', array(
            'objets'       => $objets,
            'nombreObjets' => count($objets)
        ));
    }
    
    /**
     * Affiche la liste des objets maitrisés par l'ambassadeur
     *
     * @param integer $id ID de l'ambassadeur
     *
     * @return Response
     */
    public function objetsAction( $id )
    {
        //Récupération de l'utilisateur passé en param
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $id) );
        
        $grid = $this->get('hopitalnumerique_user.grid.objet');
        $grid->setSourceCondition('ambassadeur', $id);

        return $grid->render('HopitalNumeriqueUserBundle:Ambassadeur:objets.html.twig',array(
            'user'    => $user,
            'options' => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user)
        ));
    }

    /**
     * Supprime le lien objet => ambassadeur
     *
     * @param int $id   ID de l'objet
     * @param int $user ID de l'user
     */
    public function deleteObjetAction( $id, $user )
    {
        $ambassadeur = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $user) );
        $objet       = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );

        $objet->removeAmbassadeur( $ambassadeur );
        $this->get('hopitalnumerique_objet.manager.objet')->save( $objet );

        $this->get('session')->getFlashBag()->add( 'success' ,  'La production n\'est plus maitrisée par l\'ambassadeur.' );

        return new Response('{"success":true, "url" : "'. $this->generateUrl('hopitalnumerique_user_ambassadeur_objets', array('id' => $user)).'"}', 200);
    }

    /**
     * Fancybox d'ajout d'objet à l'utilisateur
     *
     * @param integer $id ID de l'ambassadeur
     */
    public function addObjetAction( $id )
    {
        $types  = $this->get('hopitalnumerique_reference.manager.reference')->findBy(array('code'=>'CATEGORIE_OBJET'));
        $objets = $this->get('hopitalnumerique_objet.manager.objet')->getObjetsNonMaitrises( $id, $types );
        
        return $this->render('HopitalNumeriqueUserBundle:Ambassadeur:add_objet.html.twig', array(
            'objets'      => $objets,
            'ambassadeur' => $id
        ));
    }

    /**
     * Sauvegarde AJAX de la liaison objet + ambassadeur
     */
    public function saveObjetAction()
    {
        //get posted vars
        $id     = $this->get('request')->request->get('ambassadeur');
        $objets = $this->get('request')->request->get('objets');

        //bind ambassadeur
        $ambassadeur = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $id) );

        //bind objects
        $objets = $this->get('hopitalnumerique_objet.manager.objet')->findBy( array( 'id' => $objets ) );
        foreach($objets as &$objet)
            $objet->addAmbassadeur( $ambassadeur );
        
        $this->get('hopitalnumerique_objet.manager.objet')->save( $objets );
        
        $this->get('session')->getFlashBag()->add( 'success' ,  'Les productions ont été liées à l\'ambassadeur.' );

        return new Response('{"success":true, "url" : "'. $this->generateUrl('hopitalnumerique_user_ambassadeur_objets', array('id' => $id)).'"}', 200);
    }  

    /**
     * Validation de la candidature de l'utilisateur pour le questionnaire
     *
     * @param int $user
     */
    public function validationCandidatureAction( HopiUser $user )
    {
        $routeRedirection = $this->get('request')->request->get('routeRedirection');
        $routeRedirection = json_decode($routeRedirection, true);
        
        //Récupération du questionnaire de l'ambassadeur
        $idQuestionnaireAmbassadeur = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('ambassadeur');
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy( array('id' => $idQuestionnaireAmbassadeur) );

        //Changement du rôle à ambassadeur de l'utilisateur
        $role = $this->get('nodevo_role.manager.role')->findOneBy(array('role' => 'ROLE_AMBASSADEUR_7'));
        $user->setRoles( array( $role ) );

        $CMSI = $this->get('hopitalnumerique_user.manager.user')->findUsersByRoleAndRegion($user->getRegion(), 'ROLE_ARS_CMSI_4');
        //Envoie du mail de validation de la candidature
        $mail = $this->get('nodevo_mail.manager.mail')->sendValidationCandidatureAmbassadeurMail($user, $CMSI);
        $this->get('mailer')->send($mail);
        
        //Mise à jour / création de l'utilisateur
        $this->get('fos_user.user_manager')->updateUser( $user );
    
        $this->get('session')->getFlashBag()->add( 'success' ,  'La candidature au poste '. $questionnaire->getNomMinifie() .' a été validé.' );
    
        return new Response('{"success":true, "url" : "'.$this->generateUrl($routeRedirection['sauvegarde']['route'], $routeRedirection['sauvegarde']['arguments']).'"}', 200);
    }
    
    /**
     * Refus de la candidature de l'utilisateur pour le questionnaire
     *
     * @param int $idUser
     * @param int $idQuestionnaire
     */
    public function refusCandidatureAction( HopiUser $user )
    {
        $routeRedirection = $this->get('request')->request->get('routeRedirection');
        $routeRedirection = json_decode($routeRedirection, true);
        
        //Texte du refus entré dans la fancybox
        $texteRefus = $this->get('request')->request->get('texteRefus');

        //Récupération du questionnaire de l'ambassadeur
        $idQuestionnaireAmbassadeur = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->getQuestionnaireId('ambassadeur');
        $questionnaire = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findOneBy( array('id' => $idQuestionnaireAmbassadeur) );
        
        //Ajout en base du message de refus
        $refusCandidature = $this->get('hopitalnumerique_user.manager.refus_candidature')->createEmpty();
        $refusCandidature->setQuestionnaire($questionnaire);
        $refusCandidature->setUser($user);
        //Récupère l'utilsateur connecté
        $refusCandidature->setUserOrigineRefus($this->get('security.context')->getToken()->getUser());
        $refusCandidature->setMotifRefus($texteRefus);
        $refusCandidature->setDateRefus(new \DateTime());
        
        $this->get('hopitalnumerique_user.manager.refus_candidature')->save($refusCandidature);
        
        //Envoie du mail de validation de la candidature
        $CMSI = $this->get('hopitalnumerique_user.manager.user')->findUsersByRoleAndRegion($user->getRegion(), 'ROLE_ARS_CMSI_4');
        $mail = $this->get('nodevo_mail.manager.mail')->sendRefusCandidatureAmbassadeurMail($user, array('message' => $texteRefus), $CMSI);
        $this->get('mailer')->send($mail);
        
        $this->get('session')->getFlashBag()->add( 'success' ,  'La candidature au poste '. $questionnaire->getNomMinifie() .' a été refusé.' );
        
        return new Response('{"success":true, "url" : "'.$this->generateUrl($routeRedirection['sauvegarde']['route'], $routeRedirection['sauvegarde']['arguments']).'"}', 200);
    }

    /**
     * POP-IN de message de refus
     */
    public function messageRefusCandidatureAction()
    {
        return $this->render('HopitalNumeriqueUserBundle:Ambassadeur:popin_refus_candidature.html.twig');
    }

    /**
     * Page de gestion des domaines fonctionnels de l'user
     *
     * @param integer $id ID de l'user
     *
     * @return View
     */
    public function domainesFonctionnelsAction( $id )
    {
        //Récupération de l'utilisateur passé en param
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $id) );

        //récupération des domaines fonctionnels
        $domaines    = $this->get('hopitalnumerique_reference.manager.reference')->getDomainesForUser($user);

        $connaissanceAmbassadeurs = $this->get('hopitalnumerique_user.manager.connaissance_ambassadeur')->getConnaissanceAmbassadersOrderedByDomaine( $user, $domaines['ids']);
        $connaissanceReferentiels = $this->get('hopitalnumerique_reference.manager.reference')->findBy(array('code' => 'CONNAISSANCES_AMBASSADEUR'), array('order' => 'ASC'));

        return $this->render('HopitalNumeriqueUserBundle:Ambassadeur:domaines-fonctionnels.html.twig', array(
            'user'                    => $user,
            'options'                 => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user),
            'domaines'                => $domaines['domaines'],
            'connaissanceAmbassadeurs' => $connaissanceAmbassadeurs,
            'connaissanceReferentiels' => $connaissanceReferentiels
        ));
    }

    /**
     * Sauvegarde AJAX de la liaison domaine + ambassadeur
     */
    public function saveDomaineAction()
    {
        //get posted vars
        $id       = $this->get('request')->request->get('id');
        $domaines = $this->get('request')->request->get('domaines');

        //Problème avec les clés en JS : si on créé la clé 200, on va avoir un tableau de 201 entrée avec les entrées de 0 à 199 vides.
        foreach ($domaines as $key => $value)
        {
            if($value === "")
            {
                unset($domaines[$key]);
            }
        }

        //bind ambassadeur
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $id) );

        $domainesIds = array_keys($domaines);
        
        //bind objects
        $refDomaines = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'id' => $domainesIds ) );

        $connaissancesAmbassadeurs = array();
        foreach ($refDomaines as $refDomaine) 
        {
            //Vérifie si l'utilisateur a déjà renseigné ce domaine
            $connaissanceAmbassadeur = $this->get('hopitalnumerique_user.manager.connaissance_ambassadeur')->findOneBy(array('user' => $user, 'domaine' => $refDomaine));

            if(is_null($connaissanceAmbassadeur))
            {
                $connaissanceAmbassadeur = $this->get('hopitalnumerique_user.manager.connaissance_ambassadeur')->createEmpty();
                $connaissanceAmbassadeur->setUser($user);
                $connaissanceAmbassadeur->setDomaine($refDomaine);
            }

            $connaissance = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => intval($domaines[$refDomaine->getId()]) ) );
            $connaissanceAmbassadeur->setConnaissance($connaissance);

            //Ajouter au tableau des connaissances à sauvegarder
            $connaissancesAmbassadeurs[] = $connaissanceAmbassadeur;
        }

        $this->get('hopitalnumerique_user.manager.connaissance_ambassadeur')->save($connaissancesAmbassadeurs);

        $this->get('session')->getFlashBag()->add( 'success' ,  'Les connaissances métiers de l\'utilisateur ont été mis à jour.' );

        return new Response('{"success":true, "url" : "'. $this->generateUrl('hopitalnumerique_user_ambassadeur_domainesFonctionnels', array('id' => $id)).'"}', 200);
    }



    /**
     * Page de gestion des domaines fonctionnels de l'user
     *
     * @param integer $id ID de l'user
     *
     * @return View
     */
    public function connaissancesSIAction( $id )
    {
        //Récupération de l'utilisateur passé en param
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $id) );

        //récupération des domaines fonctionnels
        $domaines    = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'code' => 'CONNAISSANCES_AMBASSADEUR_SI') );
        $domainesIds = array();
        $affichageDomaines = array();

        foreach ($domaines as $domaine) 
        {
            if(!is_null($domaine->getParent()))
            {
                if(!array_key_exists($domaine->getParent()->getId(), $affichageDomaines))
                {
                    $affichageDomaines[$domaine->getParent()->getId()] = array(
                        'libelle' => $domaine->getParent()->getLibelle(),
                        'fils'    => array()
                    );    
                }

                $affichageDomaines[$domaine->getParent()->getId()]['fils'][] = $domaine;
            }
            else
            {
                if(!array_key_exists($domaine->getId(), $affichageDomaines))
                {
                    $affichageDomaines[$domaine->getId()] = array(
                        'libelle' => '',
                        'fils'    => array()
                    );    
                }
                $affichageDomaines[$domaine->getId()]['fils'][] = $domaine;
            }

            $domainesIds[] = $domaine->getId();
        }

        $connaissanceAmbassadeurs = $this->get('hopitalnumerique_user.manager.connaissance_ambassadeur_si')->getConnaissanceAmbassadersSIOrderedByDomaine( $user, $domainesIds);
        $connaissanceReferentiels = $this->get('hopitalnumerique_reference.manager.reference')->findBy(array('code' => 'CONNAISSANCES_AMBASSADEUR'), array('order' => 'ASC'));

        return $this->render('HopitalNumeriqueUserBundle:Ambassadeur:connaissancesSI.html.twig', array(
            'user'                     => $user,
            'options'                  => $this->get('hopitalnumerique_user.gestion_affichage_onglet')->getOptions($user),
            'domaines'                 => $domaines,
            'affichageDomaines'        => $affichageDomaines,
            'connaissanceAmbassadeurs' => $connaissanceAmbassadeurs,
            'connaissanceReferentiels' => $connaissanceReferentiels
        ));
    }

    /**
     * Sauvegarde AJAX de la liaison domaine + ambassadeur
     */
    public function saveConnaissancesSIAction()
    {
        //get posted vars
        $id       = $this->get('request')->request->get('id');
        $domaines = $this->get('request')->request->get('domaines');

        //Problème avec les clés en JS : si on créé la clé 200, on va avoir un tableau de 201 entrée avec les entrées de 0 à 199 vides.
        foreach ($domaines as $key => $value)
        {
            if($value === "")
            {
                unset($domaines[$key]);
            }
        }

        //bind ambassadeur
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $id) );

        $domainesIds = array_keys($domaines);
        
        //bind objects
        $refDomaines = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array( 'id' => $domainesIds ) );

        $connaissancesAmbassadeurs = array();
        foreach ($refDomaines as $refDomaine) 
        {
            //Vérifie si l'utilisateur a déjà renseigné ce domaine
            $connaissanceAmbassadeur = $this->get('hopitalnumerique_user.manager.connaissance_ambassadeur_si')->findOneBy(array('user' => $user, 'domaine' => $refDomaine));

            if(is_null($connaissanceAmbassadeur))
            {
                $connaissanceAmbassadeur = $this->get('hopitalnumerique_user.manager.connaissance_ambassadeur_si')->createEmpty();
                $connaissanceAmbassadeur->setUser($user);
                $connaissanceAmbassadeur->setDomaine($refDomaine);
            }

            $connaissance = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => intval($domaines[$refDomaine->getId()]) ) );
            $connaissanceAmbassadeur->setConnaissance($connaissance);

            //Ajouter au tableau des connaissances à sauvegarder
            $connaissancesAmbassadeurs[] = $connaissanceAmbassadeur;
        }

        $this->get('hopitalnumerique_user.manager.connaissance_ambassadeur_si')->save($connaissancesAmbassadeurs);

        $this->get('session')->getFlashBag()->add( 'success' ,  'Les connaissances SI de l\'utilisateur ont été mis à jour.' );

        return new Response('{"success":true, "url" : "'. $this->generateUrl('hopitalnumerique_user_ambassadeur_connaissancesSI', array('id' => $id)).'"}', 200);
    }

    /**
     * Liste des domaines de l'user : Fiche
     *
     * @param integer $idUser ID de l'utilisateur
     */
    public function listeDomainesAction( $idUser )
    {
        //bind ambassadeur
        $user = $this->get('hopitalnumerique_user.manager.user')->findOneBy( array('id' => $idUser) );

        $domaines = count($user->getConnaissancesAmbassadeurs()) >= 1 ? $user->getConnaissancesAmbassadeurs() : false;
        $domainesWithParent = array();

        if($domaines)
        {
            foreach ($domaines as $domaine) 
            {
                if(!array_key_exists($domaine->getDomaine()->getParent()->getId(), $domainesWithParent))
                {
                    $domainesWithParent[$domaine->getDomaine()->getParent()->getId()] = array();
                }

                $domainesWithParent[$domaine->getDomaine()->getParent()->getId()][] = $domaine;
            }
        }

        return $this->render('HopitalNumeriqueUserBundle:Ambassadeur:liste-domaines.html.twig', array(
            'domaines' => $domainesWithParent
        ));
    }
}