<?php

namespace HopitalNumerique\DomaineBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CronController extends Controller
{
    /**
     * Cron de mise à jour des autodiag
     */
    public function generationDomaineAction($id)
    {
        if ($id == 'QYUVX5X8HADKG5TRB9P43GZQ2TJ74USJBYAZEWWP2CC7MSMRK9')
        {
            ini_set("memory_limit","512M");
            ini_set('max_execution_time', 0);
        
            $domaineHN  = $this->get('hopitalnumerique_domaine.manager.domaine')->findBy(array('id' => 1));
            $domaineGen = $this->get('hopitalnumerique_domaine.manager.domaine')->findBy(array('id' => 2));

            //~~~ Users ~~~
            $users = $this->get('hopitalnumerique_user.manager.user')->findAll();
            foreach ($users as $user) 
            {
                $user->setDomaines($domaineHN);
            }

            $this->get('hopitalnumerique_user.manager.user')->save($users);

            //~~~ Outils ~~~
            $outils = $this->get('hopitalnumerique_autodiag.manager.outil')->findAll();
            foreach ($outils as $outil) 
            {
                $outil->setDomaines($outil->getId() === 17 ? $domaineGen : $domaineHN);
            }
            $this->get('hopitalnumerique_autodiag.manager.outil')->save($outils);

            //~~~ Faq ~~~
            $faqs = $this->get('nodevo_faq.manager.faq')->findAll();
            foreach ($faqs as $faq) 
            {
                $faq->setDomaines($domaineHN);
            }
            $this->get('nodevo_faq.manager.faq')->save($faq);

            //~~~ Glossaire ~~~
            $glossaires = $this->get('hopitalnumerique_glossaire.manager.glossaire')->findAll();
            foreach ($glossaires as $glossaire) 
            {
                $glossaire->setDomaines($domaineHN);
            }
            $this->get('hopitalnumerique_glossaire.manager.glossaire')->save($glossaires);

            //~~~ Objet ~~~
            $objets = $this->get('hopitalnumerique_objet.manager.objet')->findAll();
            foreach ($objets as $objet) 
            {
                $objet->setDomaines($domaineHN);
            }
            $this->get('hopitalnumerique_objet.manager.objet')->save($objets);

            //~~~ Parcours guidé ~~~
            // $parcours = $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->findAll();
            // foreach ($parcours as $parcour) 
            // {
            //     $parcour->setDomaines($domaineHN);
            // }
            // $this->get('hopitalnumerique_recherche_parcours.manager.recherche_parcours')->save($parcours);

            //~~~~ Questionnaire ~~~
            $questionnaires = $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->findAll();
            foreach ($questionnaires as $questionnaire) 
            {
                $questionnaire->setDomaines($domaineHN);
            }
            $this->get('hopitalnumerique_questionnaire.manager.questionnaire')->save($questionnaires);

            //~~~ Recherche aidée ~~~
            // $expBesoins = $this->get('hopitalnumerique_recherche.manager.expbesoin')->findAll();
            // foreach ($expBesoins as $expBesoin) 
            // {
            //     $expBesoin->setDomaine($domaineHN);
            // }
            // $this->get('hopitalnumerique_recherche.manager.expbesoin')->save($expBesoins);

            //~~~ Reference ~~~
            $references = $this->get('hopitalnumerique_reference.manager.reference')->findAll();
            foreach ($references as $reference) 
            {
                $reference->setDomaines($domaineHN);
            }
            $this->get('hopitalnumerique_reference.manager.reference')->save($references);

            return new Response('<p>Fin du traitement : OK.</p>');
        }
        
        return new Response('Clef invalide.');
    }
}