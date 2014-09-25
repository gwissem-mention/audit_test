<?php

namespace HopitalNumerique\AutodiagBundle\Controller;

use HopitalNumerique\AutodiagBundle\Entity\Outil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Outil controller.
 */
class OutilController extends Controller
{
    /**
     * Affiche la liste des Outil.
     */
    public function indexAction()
    {
        $grid = $this->get('hopitalnumerique_autodiag.grid.outil');

        return $grid->render('HopitalNumeriqueAutodiagBundle:Outil:index.html.twig');
    }

    /**
     * Affiche le formulaire d'ajout de Outil.
     */
    public function addAction( Request $request )
    {
        $outil = $this->get('hopitalnumerique_autodiag.manager.outil')->createEmpty();

        //Création du formulaire via le service
        $form = $this->createForm('hopitalnumerique_autodiag_outil', $outil );

        $processOriginaux = new ArrayCollection();
        foreach ($outil->getProcess() as $process)
            $processOriginaux->add($process);

        //si le formulaire est valide
        if ( $form->handleRequest($request)->isValid() ) {
            
            //<-- Suppression des process enlevés
            foreach ($processOriginaux as $process)
                if (!$outil->getProcess()->contains($process))
                    $this->get('hopitalnumerique_autodiag.manager.process')->delete($process);
            //-->
            
            //Save
            $this->get('hopitalnumerique_autodiag.manager.outil')->saveOutil($outil);
            
            // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
            $this->get('session')->getFlashBag()->add( 'success' , 'Outil ajouté'); 
            
            return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_outil_edit', array( 'id' => $outil->getId() ) ) );
        }

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Outil:edit.html.twig' , array(
            'form'  => $form->createView(),
            'outil' => $outil
        ));
    }

    /**
     * Affiche le formulaire d'édition de Outil.
     *
     * @param integer $id Id de Outil.
     */
    public function editAction( Outil $outil, Request $request )
    { 
        //Création du formulaire via le service
        $form = $this->createForm( 'hopitalnumerique_autodiag_outil', $outil);

        $processOriginaux = new ArrayCollection();
        $processChapitresOriginaux = array();
        foreach ($outil->getProcess() as $process)
        {
            $processOriginaux->add($process);
            $processChapitresOriginaux[$process->getId()] = new ArrayCollection();
            foreach ($process->getProcessChapitres() as $processChapitre)
                $processChapitresOriginaux[$process->getId()]->add($processChapitre);
        }

        //si le formulaire est valide
        if ( $form->handleRequest($request)->isValid() ) {

            //<-- Suppression des process et chapitres enlevés
            foreach ($processOriginaux as $process)
            {
                if (!$outil->getProcess()->contains($process))
                    $this->get('hopitalnumerique_autodiag.manager.process')->delete($process);
                else
                {
                    foreach ($processChapitresOriginaux[$process->getId()] as $processChapitre)
                    {
                        $processContientToujoursChapitre = false;
                        foreach ($outil->getProcess() as $processExistant)
                        {
                            if ($processExistant->getProcessChapitres()->contains($processChapitre))
                            {
                                $processContientToujoursChapitre = true;
                                break;
                            }
                        }
                        if (!$processContientToujoursChapitre)
                            $this->get('hopitalnumerique_autodiag.manager.process_chapitre')->delete($processChapitre);
                    }
                }
            }
            //-->
            
            //Save
            $this->get('hopitalnumerique_autodiag.manager.outil')->saveOutil($outil);

            // On envoi une 'flash' pour indiquer à l'utilisateur que l'entité est ajoutée
            $this->get('session')->getFlashBag()->add( 'info' , 'Outil mis à jour.' ); 
            
            //on redirige vers la page index ou la page edit selon le bouton utilisé
            $do = $request->request->get('do');
            return $this->redirect( ($do == 'save-close' ? $this->generateUrl('hopitalnumerique_autodiag_outil') : $this->generateUrl('hopitalnumerique_autodiag_outil_edit', array( 'id' => $outil->getId() ) ) ) );
        }

        return $this->render( 'HopitalNumeriqueAutodiagBundle:Outil:edit.html.twig' , array(
            'form'  => $form->createView(),
            'outil' => $outil
        ));
    }

    /**
     * Suppresion d'un Outil.
     * 
     * @param integer $id Id de Outil.
     * METHOD = POST|DELETE
     */
    public function deleteAction( Outil $outil )
    {
        $this->get('hopitalnumerique_autodiag.manager.outil')->delete( $outil );

        $this->get('session')->getFlashBag()->add('info', 'Suppression effectuée avec succès.' );

        return new Response('{"success":true, "url" : "'.$this->generateUrl('hopitalnumerique_autodiag_outil').'"}', 200);
    }

    /**
     * Désctivation de masse des outils
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function desactiverMassAction( $primaryKeys, $allPrimaryKeys )
    {
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_autodiag.grid.outil')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }

        //get all selected outils
        $outils = $this->get('hopitalnumerique_autodiag.manager.outil')->findBy( array('id' => $primaryKeys) );

        //get ref and Toggle State
        $ref = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 4 ) );
        $this->get('hopitalnumerique_autodiag.manager.outil')->toogleState( $outils, $ref );

        //inform user connected
        $this->get('session')->getFlashBag()->add('info', 'Désactivation effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_outil') );
    }

    /**
     * Activation de masse des outils
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function activerMassAction( $primaryKeys, $allPrimaryKeys )
    {
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_autodiag.grid.outil')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }

        //get all selected outils
        $outils = $this->get('hopitalnumerique_autodiag.manager.outil')->findBy( array('id' => $primaryKeys) );

        //get ref and Toggle State
        $ref = $this->get('hopitalnumerique_reference.manager.reference')->findOneBy( array( 'id' => 3 ) );
        $this->get('hopitalnumerique_autodiag.manager.outil')->toogleState( $outils, $ref );

        //inform user connected
        $this->get('session')->getFlashBag()->add('info', 'Activation effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_outil') );
    }

    /**
     * Suppression de masse des outils
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function deleteMassAction( $primaryKeys, $allPrimaryKeys )
    {
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_autodiag.grid.outil')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }

        //get all selected outils
        $outils = $this->get('hopitalnumerique_autodiag.manager.outil')->findBy( array('id' => $primaryKeys) );
        $this->get('hopitalnumerique_autodiag.manager.outil')->delete( $outils );

        //inform user connected
        $this->get('session')->getFlashBag()->add('info', 'Supression effectuée avec succès.' );

        return $this->redirect( $this->generateUrl('hopitalnumerique_autodiag_outil') );
    }

    /**
     * Action appelée dans le plugin "Outil" pour tinymce
     */
    public function getOutilsAction()
    {
        $outils = $this->get('hopitalnumerique_autodiag.manager.outil')->findAll();

        return $this->render('HopitalNumeriqueAutodiagBundle:Outil:getOutils.html.twig', array(
            'outils' => $outils,
            'texte'  => $this->get('request')->request->get('texte')
        ));
    }

    /**
     * Export de masse des reponses
     *
     * @param array $primaryKeys    ID des lignes sélectionnées
     * @param array $allPrimaryKeys allPrimaryKeys ???
     *
     * @return Redirect
     */
    public function exportMassAction( $primaryKeys, $allPrimaryKeys )
    {
        if($allPrimaryKeys == 1){
            $rawDatas = $this->get('hopitalnumerique_autodiag.grid.outil')->getRawData();
            foreach($rawDatas as $data)
                $primaryKeys[] = $data['id'];
        }

        //get all selected outils
        $outils = $this->get('hopitalnumerique_autodiag.manager.outil')->findBy( array('id' => $primaryKeys) );

        //prepare colonnes headers
        $colonnes = array(
            'outil'      => 'Outil',
            'chapitre0'  => 'Chapitre',
            'chapitre1'  => 'Chapitre',
            'question'   => 'Question'
        );

        $datas     = array();
        $emptyCols = array();
        foreach($outils as $outil) {
            $resultats   = $outil->getResultats();
            $colId       = 0;
            $remarques   = array();
            $lastSaves   = array();
            $validations = array();

            foreach($resultats as $resultat) {
                //add colonne ID
                $user                   = !is_null($resultat->getUser()) ? $resultat->getUser()->getPrenomNom() : 'Guest';
                $colonnes['col'.$colId] = $user;
                $emptyCols[]            = 'col'.$colId;

                $reponses = $resultat->getReponses();
                foreach($reponses as $reponse) {
                    $question = $reponse->getQuestion();

                    if ( !isset($datas[$question->getId()]) ){
                        $row               = array();
                        $row['outil']      = $outil->getTitle();
                        $row['question']   = $question->getTexte();
                        $row['chapitre1']  = $question->getChapitre()->getTitle();
                        $row['chapitre0']  = !is_null($question->getChapitre()->getParent()) ? $question->getChapitre()->getParent()->getTitle() : '';

                        $datas[$question->getId()] = $row;
                    }

                    $datas[$question->getId()]['col'.$colId] = $reponse->getValue();
                }

                $remarques['col'.$colId]   = $resultat->getRemarque();
                $lastSaves['col'.$colId]   = $resultat->getDateLastSave()->format('d/m/Y');
                $validations['col'.$colId] = !is_null($resultat->getDateValidation()) ? $resultat->getDateValidation()->format('d/m/Y') : '';

                $colId++;
            }

            $datas[] = $this->addLine( 'Date de dernière sauvegarde', $outil, $lastSaves );
            $datas[] = $this->addLine( 'Date de validation', $outil, $validations );
            $datas[] = $this->addLine( 'Remarque', $outil, $remarques );
        }

        //reparse all result to add empty cols
        foreach($datas as &$data) {
            foreach($emptyCols as $one){
                if( !isset($data[$one]) )
                    $data[$one] = '';
            }
        }

        $kernelCharset = $this->container->getParameter('kernel.charset');

        return $this->get('hopitalnumerique_autodiag.manager.outil')->exportCsv( $colonnes, $datas, 'export-resultats.csv', $kernelCharset );
    }

    /**
     * Ajoute les lignes différentes des questions
     *
     * @param [type] $titre [description]
     * @param [type] $outil [description]
     * @param [type] $datas [description]
     */
    private function addLine( $titre, $outil, $datas )
    {
        $datas['outil']     = $outil->getTitle();
        $datas['chapitre1'] = '';
        $datas['chapitre0'] = '';
        $datas['question']  = $titre;
            
        return $datas;
    }
}