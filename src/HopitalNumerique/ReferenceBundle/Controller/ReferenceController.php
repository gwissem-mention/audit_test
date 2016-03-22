<?php

namespace HopitalNumerique\ReferenceBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use HopitalNumerique\ReferenceBundle\Entity\Reference;

/**
 * Reference controller.
 */
class ReferenceController extends Controller
{
    /**
     * Affiche la liste des Reference.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_reference.grid.reference');

        return $grid->render('HopitalNumeriqueReferenceBundle:Reference:index.html.twig');
    }

    /**
     * Affichage en arborescence
     */
    public function sitemapAction()
    {
        $references = $this->get('hopitalnumerique_reference.manager.reference')->getArboFormat();
        $domainesOrderedByReference = $this->get('hopitalnumerique_reference.manager.reference')->getDomainesOrderedByReference();

        return $this->render('HopitalNumeriqueReferenceBundle:Reference:sitemap.html.twig', array(
            'references'                 => $references,
            'orderedReferences' => $this->container->get('hopitalnumerique_reference.dependency_injection.reference.tree')->getOrderedReferences(null, $this->getUser()->getDomaines()),
            'domainesOrderedByReference' => $domainesOrderedByReference
        )); 
    }

    /**
     * Affiche le formulaire d'ajout de Reference.
     */
    public function addAction()
    {
        $reference = $this->get('hopitalnumerique_reference.manager.reference')->createEmpty();
        return $this->renderForm($reference);
    }

    /**
     * Affiche le formulaire d'édition de Reference.
     */
    public function editAction( $id )
    {
        //Récupération de l'entité passée en paramètre
        $reference = $this->get('hopitalnumerique_reference.manager.reference')->findOneById($id);

        return $this->renderForm($reference);
    }

    /**
     * Sauvegarde les paramètres des activités d'expert
     */
    public function saveReferenceAjaxAction(Request $request, Reference $reference)
    {
        $montant = $request->request->get('montant');
        $reference->setLibelle($montant);
        $this->get('hopitalnumerique_reference.manager.reference')->save($reference);

        $contratModele = $this->container->get('hopitalnumerique_reference.manager.reference')->findOneByCode('ACTIVITE_EXPERT_CONTRAT_MODELE');
        if (null === $contratModele) {
            throw new \Exception('Référence "ACTIVITE_EXPERT_CONTRAT_MODELE" introuvable');
        }
        $contratModele->setLibelle($request->request->get('contratModele'));
        $this->get('hopitalnumerique_reference.manager.reference')->save($contratModele);

        $pvRecettesModele = $this->container->get('hopitalnumerique_reference.manager.reference')->findOneByCode('ACTIVITE_EXPERT_PV_RECETTES_MODELE');
        if (null === $pvRecettesModele) {
            throw new \Exception('Référence "ACTIVITE_EXPERT_PV_RECETTES_MODELE" introuvable');
        }
        $pvRecettesModele->setLibelle($request->request->get('pvRecettesModele'));
        $this->get('hopitalnumerique_reference.manager.reference')->save($pvRecettesModele);

        $response = json_encode(array('success' => true));

        return new Response($response, 200);
    }

    /**
     * Affiche le Reference en fonction de son ID passé en paramètre.
     */
    public function showAction( $id )
    {
        //Récupération de l'entité en fonction du paramètre
        $reference = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $id) );

        return $this->render('HopitalNumeriqueReferenceBundle:Reference:show.html.twig', array(
            'reference' => $reference,
        ));
    }

    /**
     * Suppression d'un Reference.
     *
     * METHOD = POST|DELETE
     */
    public function deleteAction( $id )
    {
        $reference = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $id) );
        
        if( $reference->getLock() )
            $this->get('session')->getFlashBag()->add('danger', 'Suppression impossible, la référence est verrouillée.');
        else{
            //Tentative de suppression si la référence est liée nulle part
            try
            {
                //Suppression de l'entité
                $this->get('hopitalnumerique_reference.manager.reference')->delete( $reference );
                $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );
            }
            catch ( \Exception $e) 
            {
                $this->get('session')->getFlashBag()->add('danger', 'Suppression impossible, la référence est actuellement liée et ne peut être supprimée.');
            }
        }

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_reference_reference').'"}', 200);
    }

    /**
     * Suppression de masse des références
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function deleteMassAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_reference.manager.reference')->getRawData();
            foreach($rawDatas as $data)
            {
                $primaryKeys[] = $data['id'];
            }
        }        

        $references = $this->get('hopitalnumerique_reference.manager.reference')->findBy( array('id' => $primaryKeys) );

        $this->get('hopitalnumerique_reference.manager.reference')->delete( $references );

        return $this->redirect( $this->generateUrl('hopitalnumerique_reference_reference') );
    }

    /**
     * Export CSV de la liste des références sélectionnés
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     */
    public function exportCsvAction( $primaryKeys, $allPrimaryKeys )
    {
        //get all selected Users
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_reference.grid.reference')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }
        $refs = $this->get('hopitalnumerique_reference.manager.reference')->getDatasForExport( $primaryKeys );

        $colonnes = array( 
            'id'           => 'id', 
            'libelle'      => 'Libellé du concept',
            'domaineNoms'   => 'Domaine(s)',
            'reference' => 'Est une référence',
            'inGlossaire' => 'Actif dans le glossaire',
            'etat'         => 'Etat',
            'inRecherche'    => 'Présent dans la recherche',
            'code'         => 'Code',
            'parentLibelles'     => 'Parents'
        );

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_user.manager.user')->exportCsv( $colonnes, $refs, 'export-references.csv', $kernelCharset );
    }








    /**
     * Effectue le render du formulaire Reference.
     *
     * @param Reference $item     Entité Référence
     *
     * @return Form | redirect
     */
    private function renderForm($reference)
    {
        $referenceTreeOptions = $this->container->get('hopitalnumerique_reference.dependency_injection.reference.tree')->getOptions($this->getUser()->getDomaines());
        $this->container->get('hopitalnumerique_reference.doctrine.reference.domaine_udpater')->setInitialReference($reference);

        //Création du formulaire via le service
        $form = $this->createForm('hopitalnumerique_reference_reference', $reference);

        $request = $this->get('request');

        // Si l'utilisateur soumet le formulaire
        if ('POST' == $request->getMethod()) {
            //get uploaded form datas (used to manipulate parent next)
            $formDatas = $request->request->get('hopitalnumerique_reference_reference');

            // On bind les données du form
            $form->handleRequest($request);
            $this->container->get('hopitalnumerique_reference.doctrine.reference.domaine_udpater')->updateDomaines($reference);

            //si le formulaire est valide
            if ( $form->isValid() ) 
            {
                if( isset($formDatas['parent']) && !is_null($formDatas['parent']) ){
                    $parent = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => $formDatas['parent'] ) );    
                    $reference->setParent( $parent );

                    //Mise à jour du/des domaine(s) sur l'ensemble de l'arbre d'héritage des parents
                    $family = array();
                    $daddy  = $parent;

                    //Tant qu'il y a des parents on ajoute le(s) nouveau(x) domaine(s) dessus
                    while(!is_null($daddy))
                    {
                        $childsDomaines = array();
                        //Vérifie si l'élément courant a un parent
                        $childs = $daddy->getChilds();

                        //Si on est au niveau du parent de la référence courante, on ajoute cette dernière au tableau des enfants du parent qui n'est pas encore setté
                        //si la référence est un ajout
                        if($daddy->getId() === $parent->getId() 
                            && is_null($reference->getId()))
                        {
                            if(count($childs) !== 0)
                            {
                                foreach ($childs as $child) 
                                {
                                    $childsTemp[] = $child;
                                }

                                $childsTemp[] = $reference;

                                $childs = $childsTemp;
                            }
                            else
                            {
                                $childs = array($reference);
                            }
                        }


                        foreach ($childs as $child) 
                        {
                            if(count($child->getDomaines()) !== 0)
                            {
                                foreach ($child->getDomaines() as $domaine) 
                                {
                                    if(!array_key_exists($domaine->getId(), $childsDomaines))
                                    {
                                        $childsDomaines[$domaine->getId()] = $domaine;
                                    }
                                }
                            }
                        }

                        //Vide les domaines du père pour remettre uniquement ceux des enfants (suppression d'un domaine lors de la sauvegarde n'étant plus chez aucun enfant)
                        $daddy->setDomaines(array());

                        //Récupération des domaines du parent courant pour éviter la dupplication de domaine sur une entité
                        $daddyDomainesId = $daddy->getDomainesId();
                        if(count($childsDomaines) !== 0)
                        {
                            foreach ($childsDomaines as $domaine) 
                            {
                                if(!in_array($domaine->getId(),$daddyDomainesId))
                                {
                                    //Si il n'a pas encore ce domaine, on lui ajoute
                                    $daddy->addDomaine($domaine);
                                }
                            }
                        }

                        $family[] = $daddy;

                        //Parent suivant ou null si on est au sommet de l'arbre
                        $daddy = $daddy->getParent();
                    }

                    if (count($family) > 0) {
                        $this->get('hopitalnumerique_reference.manager.reference')->save($family);
                    }
                }
                
                //test ajout ou edition
                $new = is_null($reference->getId());

                //On utilise notre Manager pour gérer la sauvegarde de l'objet
                $this->get('hopitalnumerique_reference.manager.reference')->save($reference);
                
                // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
                $this->get('session')->getFlashBag()->add( ($new ? 'success' : 'info') , 'Reference ' . ($new ? 'ajoutée.' : 'mise à jour.') ); 
                
                $do = $request->request->get('do');
                return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_reference_reference') : $this->generateUrl('hopitalnumerique_reference_reference_edit', array( 'id' => $reference->getId() ) ) ) );
            }
        }

        return $this->render('HopitalNumeriqueReferenceBundle:Reference:edit.html.twig', array(
            'form'      => $form->createView(),
            'reference' => $reference,
            'referenceTreeOptions' => json_encode($referenceTreeOptions)
        ));
    }
}
