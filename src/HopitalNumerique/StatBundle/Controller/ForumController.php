<?php

namespace HopitalNumerique\StatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author    Yann 'YRO' Rochereau
 * @copyright Nodevo
 */
class ForumController extends Controller
{
    /**
     * Affiche les tableaux des statistiques
     */
    public function indexAction( )
    {
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/Forum/bloc.html.twig', array());
    }

    /**
     * Génération du tableau à exporter
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * 
     * @return View
     */
    public function generateTableauAction( Request $request )
    {
        //Récupération de la requete
        $dateDebut    = $request->request->get('datedebut-forum');
        $dateFin      = $request->request->get('dateFin-forum');

        //Récupération des dates sous forme DateTime
        $dateDebutDateTime = $dateDebut === "" ? null : new \DateTime($dateDebut);
        $dateFinDateTime   = $dateFin   === "" ? null : new \DateTime($dateFin);

        $stats = $this->get('hopitalnumerique_stat.manager.statrecherche')->getStatsForum($dateDebutDateTime, $dateFinDateTime);
        
        return $this->render('HopitalNumeriqueStatBundle:Back:partials/Forum/tableau.html.twig', array(
            'stats' => $stats,
            'forumNames' => array(
                "1" => "Forum public",
                "2" => "Forum des experts",
                "3" => "Forum des ambassadeurs",
                "4" => "Forum des CMSI",
            )
        ));
    }

    /**
     * Génération du tableau à exporter
     *
     * @param  Symfony\Component\HttpFoundation\Request  $request
     * 
     * @return View
     */
    public function exportCSVAction( Request $request )
    {
        //Récupération de la requete
        $dateDebut    = $request->request->get('datedebut-forum');
        $dateFin      = $request->request->get('dateFin-forum');

        //Récupération des dates sous forme DateTime
        $dateDebutDateTime = $dateDebut === "" ? null : new \DateTime($dateDebut);
        $dateFinDateTime   = $dateFin   === "" ? null : new \DateTime($dateFin);        

        //Colonnes communes
        $colonnes = array(
            'boards' => 'Boards',
            'fils'   => 'Fils',
            'nbFils' => 'Nombre de fils',
            'nbPost' => 'Nombre de posts',
            'NbVues' => 'Nombre de vues total'
        );

        $kernelCharset = $this->container->getParameter('kernel.charset');
        $stats = $this->get('hopitalnumerique_stat.manager.statrecherche')->getStatsForum($dateDebutDateTime, $dateFinDateTime);
        $datas = $this->genereDatas( $stats );

        return $this->get('hopitalnumerique_stat.manager.statrecherche')->exportCsv( $colonnes, $datas, 'export-forum.csv', $kernelCharset );
    }
    
    /**
     * Génère le tableau passé à exportCSV
     */
    private function genereDatas( $stats )
    {
        $datas = array();
        foreach($stats as $forum )
        {
            foreach( $forum as $boards )
            {
                $datas[] = array(
                    'boards' => $boards['name'],
                    'fils'   => '',
                    'nbFils' => count($boards['topics']),
                    'nbPost' => $boards['nbTopics'],
                    'NbVues' => $boards['nbVuesTotal']
                );
                foreach( $boards['topics'] as $topic )
                {
                    $datas[] = array(
                        'boards' => '',
                        'fils'   => $topic['name'],
                        'nbFils' => '',
                        'nbPost' => $topic['nbPosts'],
                        'NbVues' => $topic['nbVues']
                    );
                }
                
                $datas[] = array(
                    'boards' => '',
                    'fils'   => '',
                    'nbFils' => '',
                    'nbPost' => '',
                    'NbVues' => ''
                );
            }
                
            $datas[] = array(
                'boards' => '',
                'fils'   => '',
                'nbFils' => '',
                'nbPost' => '',
                'NbVues' => ''
            );
        }
        
        return $datas;
    }
}
