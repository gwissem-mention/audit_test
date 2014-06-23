<?php

namespace HopitalNumerique\AdminBundle\Controller;

use Gedmo\Loggable\Entity\LogEntry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * 
 */
class DefaultController extends Controller
{
    /**
     * Index Action
     */
    public function indexAction()
    {
        return $this->render('HopitalNumeriqueAdminBundle:Default:index.html.twig', array());
    }




    public function histoAction()
    {
        $objets = $this->get('hopitalnumerique_objet.manager.objet')->findAll();

        foreach($objets as $objet) {
            //build array
            $datas = array();
            $datas['titre']                = $objet->getTitre();
            $datas['alias']                = $objet->getAlias();
            $datas['synthese']             = $objet->getSynthese();
            $datas['resume']               = $objet->getResume();
            $datas['path']                 = $objet->getPath();
            $datas['path2']                = $objet->getPath2();
            $datas['pathEdit']             = $objet->getPathEdit();
            $datas['commentaires']         = $objet->getCommentaires();
            $datas['notes']                = $objet->getNotes();
            $datas['dateDebutPublication'] = $objet->getDateDebutPublication();
            $datas['dateFinPublication']   = $objet->getDateFinPublication();
            $datas['dateModification']     = $objet->getDateModification();
            $datas['isInfraDoc']           = $objet->isInfraDoc();
            $datas['isArticle']            = $objet->isArticle();
            $datas['vignette']             = $objet->getVignette();
            $datas['referencement']        = $objet->getReferencement();
            $datas['etat']                 = array('id' => $objet->getEtat()->getId());

            $logEntry = new LogEntry;
            $logEntry->setAction('create');
            $logEntry->setObjectClass('HopitalNumerique\ObjetBundle\Entity\Objet');
            $logEntry->setObjectId( $objet->getId() );
            $logEntry->setUsername( 'nodevo' );
            $logEntry->setLoggedAt();
            $logEntry->setVersion(1);
            $logEntry->setData( $datas );

            $this->getDoctrine()->getEntityManager()->persist( $logEntry );
        }
        $this->getDoctrine()->getEntityManager()->flush();

        $users = $this->get('hopitalnumerique_user.manager.user')->findAll();
        foreach($users as $user) {
            //build array
            $datas = array();

            $datas['username']                        = $user->getUsername();
            $datas['email']                           = $user->getEmail();
            $datas['nom']                             = $user->getNom();
            $datas['prenom']                          = $user->getPrenom();
            $datas['region']                          = $user->getRegion() ? array( 'id' => $user->getRegion()->getId() ) : null;
            $datas['departement']                     = $user->getDepartement() ? array( 'id' => $user->getDepartement()->getId() ) : null;
            $datas['etat']                            = $user->getEtat() ? array( 'id' => $user->getEtat()->getId() ) : null;
            $datas['titre']                           = $user->getTitre() ? array( 'id' => $user->getTitre()->getId() ) : null;
            $datas['civilite']                        = $user->getCivilite() ? array( 'id' => $user->getCivilite()->getId() ) : null;
            $datas['telephoneDirect']                 = $user->getTelephoneDirect();
            $datas['telephonePortable']               = $user->getTelephonePortable();
            $datas['contactAutre']                    = $user->getContactAutre();
            $datas['statutEtablissementSante']        = $user->getStatutEtablissementSante() ? array( 'id' => $user->getStatutEtablissementSante()->getId() ) : null;
            $datas['etablissementRattachementSante']  = $user->getEtablissementRattachementSante() ? array( 'id' => $user->getEtablissementRattachementSante()->getId() ) : null;
            $datas['autreStructureRattachementSante'] = $user->getAutreStructureRattachementSante();
            $datas['fonctionDansEtablissementSante']  = $user->getFonctionDansEtablissementSante();
            $datas['profilEtablissementSante']        = $user->getProfilEtablissementSante() ? array( 'id' => $user->getProfilEtablissementSante()->getId() ) : null;
            $datas['nomStructure']                    = $user->getNomStructure();
            $datas['fonctionStructure']               = $user->getFonctionStructure();
            $datas['archiver']                        = $user->getArchiver();
            $datas['raisonDesinscription']            = $user->getRaisonDesinscription();
            $datas['path']                            = $user->getPath();


            $logEntry = new LogEntry;
            $logEntry->setAction('create');
            $logEntry->setObjectClass('HopitalNumerique\UserBundle\Entity\User');
            $logEntry->setObjectId( $user->getId() );
            $logEntry->setUsername( 'nodevo' );
            $logEntry->setLoggedAt();
            $logEntry->setVersion(1);
            $logEntry->setData( $datas );

            $this->getDoctrine()->getEntityManager()->persist( $logEntry );
        }
        $this->getDoctrine()->getEntityManager()->flush();

        die('ok');
    }
}