<?php
namespace HopitalNumerique\AutodiagBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CronController extends Controller
{
    /**
     * Cron de mise à jour des autodiag
     */
    public function autodiagAction($id)
    {
        if ($id == '12FHFURJYIHOLP23FKVIDUEZAGEIKDRCTUFT')
        {
            //on vide tout les autodiags déjà liés, et on prépare le tableau de mise à jour
            $objets      = $this->get('hopitalnumerique_objet.manager.objet')->findAll();
            $productions = array();
            foreach($objets as $objet){
                $objet->setAutodiags( array() );
                $productions[ $objet->getId() ]['objet']  = $objet;
                $productions[ $objet->getId() ]['outils'] = array();
            }
        
            $this->getDoctrine()->getManager()->flush();

            //on récupère tous les outils et on cherche les objets liés
            $outils = $this->get('hopitalnumerique_autodiag.manager.outil')->findAll();
            foreach($outils as $outil){
                //Regroupement des références des chapitres et des questions confondues
                $refs = array();

                //parcours des chapitres
                $chapitres = $outil->getChapitres();
                foreach($chapitres as $chapitre){
                    //add refs
                    $references = $chapitre->getReferences();
                    foreach($references as $reference)
                        $refs[] = $reference->getReference()->getId();

                    //parcours des questions
                    $questions = $chapitre->getQuestions();
                    foreach($questions as $question){
                        //add refs
                        $references = $question->getReferences();
                        foreach($references as $reference)
                            $refs[] = $reference->getReference()->getId();
                    }
                }

                //get Objets with refs
                $objets = $this->container->get('hopitalnumerique_recherche.manager.search')->getObjetsForCronAutodiag( array_unique($refs) );
                if( count($objets) != 0 ){
                    foreach($objets as $objet)
                        $productions[ $objet['id'] ]['outils'][] = $outil->getId();
                }
                
                //updates objets
                foreach($productions as $production){
                    $objet = $production['objet'];
                    $objet->setAutodiags( $production['outils'] );
                }
                $this->getDoctrine()->getManager()->flush();
            }

            return new Response('<p>Fin du traitement : OK.</p>');
        }
        
        return new Response('Clef invalide.');
    }
}