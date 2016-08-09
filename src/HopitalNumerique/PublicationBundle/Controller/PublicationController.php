<?php

namespace HopitalNumerique\PublicationBundle\Controller;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\DomaineBundle\Entity\Domaine;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\ReferenceBundle\Entity\EntityHasReference;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PublicationController extends Controller
{
    /**
     * Objet Action. Ajout ?pdf=1 pour la vue PDF.
     * @param Request $request
     * @param Objet $objet
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Exception
     */
    public function objetAction(Request $request, Objet $objet)
    {
        $isPdf = ($request->query->has('pdf') && '1' == $request->query->get('pdf'));
        $domaine = $this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();
        $request->getSession()->set('urlToRedirect', $request->getUri());

        if (!in_array($domaine->getId(), $objet->getDomainesId())) {
            throw $this->createNotFoundException("La publication n'appartient pas au domaine courant.");
        }

        //objet visualisation
        if (!$this->get('security.context')->isGranted('ROLE_ADMINISTRATEUR_1')) {
            $objet->setNbVue(($objet->getNbVue() + 1));
            $this->get('hopitalnumerique_objet.manager.objet')->save($objet);
        }

        //Si l'user connecté à le rôle requis pour voir l'objet
        if ($this->checkAuthorization($objet) === false) {
            $urlPublication = $this->generateUrl(
                'hopital_numerique_publication_publication_objet',
                array('id' => $objet->getId())
            );
            $urlPublication = rtrim(strtr(base64_encode($urlPublication), '+/', '-_'), '=');
            return $this->redirect($this->generateUrl('account_login', array('urlToRedirect' => $urlPublication)));
        }

        //Types objet
        $types = $this->get('hopitalnumerique_objet.manager.objet')->formatteTypes($objet->getTypes());

        //set Consultation entry
        if (!$objet->isArticle()) {
            $this->get('hopitalnumerique_objet.manager.consultation')->consulted($domaine, $objet);
        }

        //get Contenus : for sommaire
        $contenus = $objet->isInfraDoc()
            ? $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjet($objet->getId())
            : array();

        $references = $this->container->get('hopitalnumerique_reference.manager.entity_has_reference')
            ->findByEntityTypeAndEntityIdAndDomaines(
                $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getEntityType($objet),
                $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getEntityId($objet),
                [$this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()]
            );

        $referencesInDomaine = [];
        $objetDomaines = $objet->getDomaines();
        foreach ($objetDomaines as $domaine) {
            $domaineReference = $this->container->get('hopitalnumerique_reference.manager.entity_has_reference')
                ->findByEntityTypeAndEntityIdAndDomaines(
                    $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getEntityType($objet),
                    $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getEntityId($objet),
                    [$domaine]
                );
            $referenceString = join(',', array_map(function (EntityHasReference $reference) {
                return $reference->getReference()->getId();
            }, $domaineReference));
            $referencesInDomaine[$domaine->getId()] = $referenceString;
        }

        $reader = $this->get('hopitalnumerique_recherche.doctrine.referencement.reader');

        $userRelated = $reader->getRelatedObjectsByType($objet, Entity::ENTITY_TYPE_AMBASSADEUR);
        shuffle($userRelated);
        $topicRelated = (Domaine::DOMAINE_HOPITAL_NUMERIQUE_ID == $this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()->getId() ? $reader->getRelatedObjectsByType($objet, Entity::ENTITY_TYPE_FORUM_TOPIC) : []);
        shuffle($topicRelated);

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Publication:objet.html.twig', array(
            'objet'        => $objet,
            'note'         => $this->get('hopitalnumerique_objet.doctrine.note_reader')->getNoteByObjetAndUser($objet, $this->getUser()),
            'types'        => $types,
            'contenus'     => $contenus,
            'meta'         => $this->get('hopitalnumerique_recherche.manager.search')->getMetas($references, $objet->getResume()),
            'ambassadeurs' => $this->getAmbassadeursConcernes($objet->getId()),
            'productionsLiees' => $this->get('hopitalnumerique_objet.dependency_injection.production_liee')->getFormattedProductionsLiees($objet),
            'parcoursGuides' => $this->get('hopitalnumerique_rechercheparcours.dependency_injection.parcours_guide_lie')->getFormattedParcoursGuidesLies($objet),
            'topicRelated' => array_slice($topicRelated, 0, 3),
            'userRelated' => array_slice($userRelated, 0, 3),
            'is_pdf' => $isPdf,
            'referencesStringByDomaine' => $referencesInDomaine
        ));
    }

    /**
     * PDF.
     */
    public function pdfAction(Request $request, $entityType, $entityId)
    {
        if (Entity::ENTITY_TYPE_OBJET == $entityType) {
            $objet = $this->container->get('hopitalnumerique_objet.manager.objet')->findOneById($entityId);
            $pdfUrl = $this->generateUrl(
                'hopital_numerique_publication_publication_objet',
                [
                    'id' => $entityId,
                    'pdf' => 1
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $fileName = 'ANAP-' . $objet->getAlias() . '.pdf';
        } elseif (Entity::ENTITY_TYPE_CONTENU == $entityType) {
            $contenu = $this->container->get('hopitalnumerique_objet.manager.contenu')->findOneById($entityId);
            $pdfUrl = $this->generateUrl(
                'hopital_numerique_publication_publication_contenu_without_alias',
                [
                    'id' => $contenu->getObjet()->getId(),
                    'idc' => $contenu->getId(),
                    'pdf' => 1
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $fileName = 'ANAP-' . $contenu->getAlias() . '.pdf';
        } else {
            throw new \Exception('Type d\'entité "'.$entityType.'" non reconnu pour la génération du PDF.');
        }

        $pdfOptions = array(
            'encoding'         => 'UTF-8',
            'javascript-delay' => 1000,
            'margin-top'       => '15',
            'margin-bottom'    => '25',
            'margin-right'     => '15',
            'margin-left'      => '15',
            'header-spacing'   => '2',
            'header-left'      => date('d/m/Y'),
            'header-right'     => 'Page [page] / [toPage]',
            'header-font-size' => '10',
            'footer-spacing'   => '10',
            'page-width' => '1024px',
            'footer-html'      => '<p style="font-size:10px;text-align:center;color:#999"> &copy; ANAP<br>Ces contenus extraits de l\'ANAP sont diffus&eacute;s gratuitement.<br>Toutefois, leur utilisation ou citation est soumise &agrave; l\'inscription de la mention suivante : "&copy; ANAP"</p>'
        );

        return new Response(
            $this->container->get('knp_snappy.pdf')->getOutput($pdfUrl, $pdfOptions),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName
            ]
        );
    }

    /**
     * Contenu Action
     *
     * @param $id ID de l'objet
     * @param $idc ID du contenu
     */
    public function contenuAction(Request $request, $id, $alias = null, $idc, $aliasc = null)
    {
        $domaine = $this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get();
        $request->getSession()->set('urlToRedirect', $request->getUri());

        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $id ) );

        if (!in_array($domaine->getId(), $objet->getDomainesId())) {
            throw $this->createNotFoundException("La publication n'appartient pas au domaine courant.");
        }

        //Si l'user connecté à le rôle requis pour voir l'objet
        if( $this->checkAuthorization( $objet ) === false )
        {
            if(is_null($alias))
            {
                $urlPublication = $this->generateUrl('hopital_numerique_publication_publication_contenu_without_alias', array('id' => $id, 'idc' => $idc));
            }
            else
            {
                $urlPublication = $this->generateUrl('hopital_numerique_publication_publication_contenu', array('id' => $id, 'idc' => $idc, 'alias' => $alias, 'aliasc' => $aliasc));
            }
            $urlPublication = rtrim(strtr(base64_encode($urlPublication), '+/', '-_'), '=');
            return $this->redirect( $this->generateUrl('account_login', array('urlToRedirect' => $urlPublication) ) );
            // return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );
        }

        //on récupère le contenu
        $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array( 'id' => $idc ) );

        $prefix  = $this->get('hopitalnumerique_objet.manager.contenu')->getPrefix($contenu);

        //objet visualisation
        if(!$this->get('security.context')->isGranted('ROLE_ADMINISTRATEUR_1'))
        {
            $contenu->setNbVue( ($contenu->getNbVue() + 1) );
            $this->get('hopitalnumerique_objet.manager.contenu')->save($contenu);
        }

        //set Consultation entry
        if(!$objet->isArticle())
        {
            $this->get('hopitalnumerique_objet.manager.consultation')->consulted( $domaine, $contenu, true );
        }

        $contenuTemp      = $contenu;
        $breadCrumbsArray = array();

        $contenusNonVidesTries  = $this->get('hopitalnumerique_objet.manager.contenu')->getContenusNonVidesTries( $objet );

        $precedent      = $this->get('hopitalnumerique_objet.manager.contenu')->getPrecedent( $contenusNonVidesTries, $contenu );
        $precedentOrder = $this->get('hopitalnumerique_objet.manager.contenu')->getFullOrder($precedent);
        $suivant        = $this->get('hopitalnumerique_objet.manager.contenu')->getSuivant( $contenusNonVidesTries, $contenu );
        $suivantOrder   = $this->get('hopitalnumerique_objet.manager.contenu')->getFullOrder($suivant);

        //Types objet
        $types = $this->get('hopitalnumerique_objet.manager.objet')->formatteTypes( $objet->getTypes() );

        //get Contenus : for sommaire
        $contenus = $objet->isInfraDoc() ? $this->get('hopitalnumerique_objet.manager.contenu')->getArboForObjet( $id ) : array();

        //Ajout du contenu courant
        $breadCrumbsArray[] = array(
            'label'   => $this->get('hopitalnumerique_objet.manager.contenu')->getPrefix($contenu) . ' ' . $contenu->getTitre(),
            'contenu' => $contenu
        );
        $breadCrumbs      = "";

        while(!is_null($contenuTemp->getParent()))
        {
            $contenuTemp      = $contenuTemp->getParent();
            array_unshift($breadCrumbsArray, array(
                    'label'   => $this->get('hopitalnumerique_objet.manager.contenu')->getPrefix($contenuTemp) . ' ' . $contenuTemp->getTitre(),
                    'contenu' => $contenuTemp
                )
            );
        }

        $references = $this->container->get('hopitalnumerique_reference.manager.entity_has_reference')->findByEntityTypeAndEntityIdAndDomaines(
            $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getEntityType($contenu),
            $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getEntityId($contenu),
            [$this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()]
        );
        $meta = $this->get('hopitalnumerique_recherche.manager.search')->getMetas($references, $contenu->getContenu() );

        $referencesInDomaine = [];
        $objetDomaines = $objet->getDomaines();
        foreach ($objetDomaines as $domaine) {
            $domaineReference = $this->container->get('hopitalnumerique_reference.manager.entity_has_reference')
                ->findByEntityTypeAndEntityIdAndDomaines(
                    $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getEntityType($objet),
                    $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getEntityId($objet),
                    [$domaine]
                );
            $referenceString = join(',', array_map(function (EntityHasReference $reference) {
                return $reference->getReference()->getId();
            }, $domaineReference));
            $referencesInDomaine[$domaine->getId()] = $referenceString;
        }

        $ambassadeurs = $this->getAmbassadeursConcernes( $objet->getId() );

        $reader = $this->get('hopitalnumerique_recherche.doctrine.referencement.reader');

        $userRelated = $reader->getRelatedObjectsByType($objet, Entity::ENTITY_TYPE_AMBASSADEUR);
        shuffle($userRelated);
        $topicRelated = $reader->getRelatedObjectsByType($objet, Entity::ENTITY_TYPE_FORUM_TOPIC);
        shuffle($topicRelated);

        //render
        return $this->render('HopitalNumeriquePublicationBundle:Publication:objet.html.twig', array(
            'objet'            => $objet,
            'note'             => $this->container->get('hopitalnumerique_objet.doctrine.note_reader')->getNoteByContenuAndUser($contenu, $this->getUser()),
            'contenus'         => $contenus,
            'types'            => $types,
            'contenu'          => $contenu,
            'breadCrumbsArray' => $breadCrumbsArray,
            'prefix'           => $prefix,
            'meta'             => $meta,
            'ambassadeurs'     => $ambassadeurs,
            'precedent'        => $precedent,
            'precedentOrder'   => $precedentOrder,
            'suivant'          => $suivant,
            'suivantOrder'     => $suivantOrder,
            'productionsLiees' => $this->get('hopitalnumerique_objet.dependency_injection.production_liee')->getFormattedProductionsLiees($contenu),
            'parcoursGuides'   => $this->container->get('hopitalnumerique_rechercheparcours.dependency_injection.parcours_guide_lie')->getFormattedParcoursGuidesLies($objet),
            'topicRelated'     => array_slice($topicRelated, 0, 3),
            'userRelated'      => array_slice($userRelated, 0, 3),
            'is_pdf' => ($request->query->has('pdf') && '1' == $request->query->get('pdf')),
            'referencesStringByDomaine' => $referencesInDomaine
        ));
    }

    /**
     * Article Action
     */
    public function articleAction(Request $request, $categorie, $id, $alias)
    {
        $domaineId = $request->getSession()->get('domaineId');
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array( 'id' => $id ) );
        $request->getSession()->set('urlToRedirect', $request->getUri());

        if (!in_array($domaineId, $objet->getDomainesId())) {
            throw $this->createNotFoundException("La publication n'appartient pas au domaine courant.");
        }

        //Si l'user connecté à le rôle requis pour voir l'objet
        if( $this->checkAuthorization( $objet ) === false )
        {
            $urlPublication = $this->generateUrl('hopital_numerique_publication_publication_article', array('categorie' => $categorie, 'id' => $id, 'alias' => $alias));
            $urlPublication = rtrim(strtr(base64_encode($urlPublication), '+/', '-_'), '=');
            return $this->redirect( $this->generateUrl('account_login', array('urlToRedirect' => $urlPublication) ) );
            // return $this->redirect( $this->generateUrl('hopital_numerique_homepage') );
        }

        //on récupère l'item de menu courant
        $routeName   = $request->get('_route');
        $routeParams = json_encode($request->get('_route_params'));
        $item        = $this->get('nodevo_menu.manager.item')->findOneBy( array('route'=>$routeName, 'routeParameters'=>$routeParams) );

        //on récupère les actus
        $categories = $this->get('hopitalnumerique_reference.manager.reference')->findByParent($this->get('hopitalnumerique_reference.manager.reference')->findOneById(188));

        //get Type
        $types = $this->get('hopitalnumerique_objet.manager.objet')->formatteTypes( $objet->getTypes() );

        $references = $this->container->get('hopitalnumerique_reference.manager.entity_has_reference')->findByEntityTypeAndEntityIdAndDomaines(
            $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getEntityType($objet),
            $this->container->get('hopitalnumerique_core.dependency_injection.entity')->getEntityId($objet),
            [$this->container->get('hopitalnumerique_domaine.dependency_injection.current_domaine')->get()]
        );
        //render
        return $this->render('HopitalNumeriquePublicationBundle:Publication:articles.html.twig', array(
            'objet'      => $objet,
            'meta'       => $this->get('hopitalnumerique_recherche.manager.search')->getMetas($references, $objet->getResume() ),
            'menu'       => $item ? $item->getMenu()->getAlias() : null,
            'categories' => $categories,
            'types'      => $types
        ));
    }

    /**
     * Affiche la synthèse de l'objet dans une grande popin
     */
    public function syntheseAction($id)
    {
        $objet = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $id) );

        //test si l'user connecté à le rôle requis pour voir la synthèse
        $user   = $this->get('security.context')->getToken()->getUser();
        $role   = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $params = array();
        if( $this->get('hopitalnumerique_objet.manager.objet')->checkAccessToObjet($role, $objet) )
            $params['objet'] = $objet;

        return $this->render('HopitalNumeriquePublicationBundle:Publication:synthese.html.twig', $params);
    }






    /**
     * Retorune le type de la prod
     *
     * @param  [type] $objet [description]
     *
     * @return [type]
     */
    private function getType( $objet )
    {
        $type  = array();
        $types = $objet->getTypes();

        foreach ($types as $one) {
            $parent = $one->getFirstParent();
            if( !is_null($parent) && $parent->getId() == 175 )
                $type[] = $one->getLibelle();
        }
        //reformatte proprement les types
        $type = implode(' ♦ ', $type);

        return $type;
    }

    /**
     * Build productions with authorizations
     *
     * @param  [type] $prodLiees [description]
     *
     * @return [type]
     */
    private function getProductionsAssocies( $prodLiees )
    {
        if (null === $prodLiees) {
            $prodLiees = [];
        }

        $productions = array();
        foreach( $prodLiees as $prod){
            $tab = explode(':', $prod);

            //switch Objet / Infra-doc
            if( $tab[0] == 'PUBLICATION' ){
                $objet   = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $tab[1] ) );
                $contenu = false;
            }else if( $tab[0] == 'INFRADOC' ){
                $contenu = $this->get('hopitalnumerique_objet.manager.contenu')->findOneBy( array('id' => $tab[1] ) );
                $objet   = $contenu->getObjet();
            }else if( $tab[0] == 'ARTICLE' ){
                $objet   = $this->get('hopitalnumerique_objet.manager.objet')->findOneBy( array('id' => $tab[1] ) );
                $contenu = false;
            }

            if( $this->checkAuthorization( $objet ) === true ){
                $production        = new \StdClass;
                $production->id    = $objet->getId();
                $production->alias = $objet->getAlias();
                $production->source = $objet->getSource();

                //Cas Objet
                if( $contenu === false )
                {
                    //formate datas
                    $production->titre    = $objet->getTitre();
                    $production->created  = $objet->getDateCreation();
                    $production->objet    = true;
                    $resume               = explode('<!-- pagebreak -->', $objet->getResume() );
                    $production->synthese = $objet->getSynthese();
                    $production->idc      = null;
                }
                else
                {
                    //formate datas
                    $production->idc      = $contenu->getId();
                    $production->aliasc   = $contenu->getAlias();
                    $production->titre    = $contenu->getTitre();
                    $production->created  = $contenu->getDateCreation();
                    $production->objet    = false;
                    $production->synthese = null;
                    $resume               = explode('<!-- pagebreak -->', $contenu->getContenu() );
                }

                $production->resume   = $resume[0];
                $production->updated  = false;
                $production->new      = false;
                $production->type     = $this->getType($objet);

                $productions[] = $production;
            }
        }

        $request       = $this->get('request');
        $domaineId     = $request->getSession()->get('domaineId');

        //update status updated + new
        $user        = $this->get('security.context')->getToken()->getUser();
        $productions = $this->get('hopitalnumerique_objet.manager.consultation')->updateProductionsWithConnectedUser( $domaineId, $productions, $user );

        return $productions;
    }

    /**
     * Retourne la liste des ambassadeurs concernés par la production
     *
     * @param Objet $objet La production consultée
     *
     * @return array
     */
    private function getAmbassadeursConcernes( $objet )
    {
        //get connected user and his region
        $user   = $this->get('security.context')->getToken()->getUser();
        $region = $user === 'anon.' ? false : $user->getRegion();

        return $this->get('hopitalnumerique_user.manager.user')->getAmbassadeursByRegionAndProduction( $region, $objet );
    }

    /**
     * Vérifie que l'objet est accessible à l'user connecté ET que l'objet est toujours bien publié
     *
     * @param Objet $objet L'objet
     *
     * @return boolean
     */
    private function checkAuthorization( $objet )
    {
        $user    = $this->get('security.context')->getToken()->getUser();
        $role    = $this->get('nodevo_role.manager.role')->getUserRole($user);
        $message = 'Vous n\'avez pas accès à cette publication.';

        //test si l'user connecté à le rôle requis pour voir l'objet
        if( !$this->get('hopitalnumerique_objet.manager.objet')->checkAccessToObjet($role, $objet) ) {
            return false;
        }

        //test si l'objet est actif : état actif === 3
        if( $objet->getEtat()->getId() != 3 ){
            $this->get('session')->getFlashBag()->add('warning', $message );
            return false;
        }

        return true;
    }
}
