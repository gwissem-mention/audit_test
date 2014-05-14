<?php

namespace HopitalNumerique\ModuleBundle\Manager;

use Nodevo\ToolsBundle\Manager\Manager as BaseManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Manager de l'entité Session.
 * 
 * @author Gaetan MELCHILSEN
 * @copyright Nodevo
 */
class SessionManager extends BaseManager
{
    protected $_class = 'HopitalNumerique\ModuleBundle\Entity\Session';

    /**
     * Override : Récupère les données pour le grid sous forme de tableau
     *
     * @return array
     * 
     * @author Gaetan MELCHILSEN
     * @copyright Nodevo
     */
    public function getDatasForGrid( $condition = null )
    {
        $sessions = $this->getRepository()->getDatasForGrid( $condition )->getQuery()->getResult();

        $result = array();

        foreach ($sessions as $key => $session) 
        {
            $nbInscritsAccepte   = 0;
            $nbInscritsEnAttente = 0;
            $nbPlacesRestantes   = $session->getNombrePlaceDisponible();

            foreach ($session->getInscriptions() as $inscription) 
            {
                if($inscription->getEtatInscription()->getId() === 406)
                    $nbInscritsEnAttente++;
                elseif($inscription->getEtatInscription()->getId() === 407)
                {
                    $nbInscritsAccepte++;
                    $nbPlacesRestantes--;
                }
            }

            $result[$key] = array(
                'id'                       => $session->getId(),
                'dateOuvertureInscription' => $session->getDateOuvertureInscription(),
                'dateFermetureInscription' => $session->getDateFermetureInscription(),
                'dateSession'              => $session->getDateSession(),
                'duree'                    => $session->getDuree(),
                'horaires'                 => $session->getHoraires(),
                'nbInscrits'               => $nbInscritsAccepte,
                'nbInscritsEnAttente'      => $nbInscritsEnAttente,
                'placeRestantes'           => $nbPlacesRestantes . '/' . $session->getNombrePlaceDisponible(),
                'etat'                     => $session->getEtat()->getLibelle()
            );
        }

        return $result;
    }

    /**
     * Retourne la liste des sessions du formateur
     *
     * @param User $user L'utilisateur concerné
     * 
     * @return array
     */
    public function getSessionsForFormateur( $user )
    {
        return $this->getRepository()->getSessionsForFormateur( $user )->getQuery()->getResult();
    }

    /**
     * Export CSV du grid selon les colonnes
     *
     * @param array  $colonnes Liste des colonnes à exporter
     * @param array  $datas    Tableau de données
     * @param string $filename Nom du fichier CSV exporté
     * @param [type] $kernelCharset [description]
     *
     * @return Response
     */
    public function customExportCsv( $colonnes, $datas, $filename, $kernelCharset )
    {
        // Array to csv (copy from APY\DataGridBundle\Grid\Export\DSVExport.php)
        $outstream = fopen("php://temp", 'r+');

        //Ajout de la colonne d'en-têtes
        $firstLine = array_values($colonnes);
        fputcsv($outstream, $firstLine, ';', '"');

        //creation du FlatArray pour la conversion en CSV
        $keys      = array_keys($colonnes);
        $flatArray = array();
        foreach($datas as $data) {
            $ligne = array();
            foreach($keys as $key) {
                $val     = $data[$key];
                $ligne[] = is_null($val) ? '' : $val;
            }

            $flatArray[] = $ligne;
        }

        //génération du CSV
        foreach ($flatArray as $line)
            fputcsv($outstream, $line, ';', '"');

        //on replace le buffer au début pour refaire la lecture
        rewind($outstream);

        //génération du contenu
        $content = '';
        while (($buffer = fgets($outstream)) !== false)
            $content .= $buffer;

        fclose($outstream);

        // Charset and Length
        $charset = 'ISO-8859-1';
        if ($charset != $kernelCharset && function_exists('mb_strlen')) {
            $content  = mb_convert_encoding($content, $charset, $kernelCharset);
            $filesize = mb_strlen($content, '8bit');
        } else {
            $filesize = strlen($content);
            $charset  = $kernelCharset;
        }

        //build header
        $headers = array(
            'Content-Description'       => 'File Transfer',
            'Content-Type'              => 'text/comma-separated-values',
            'Content-Disposition'       => sprintf('attachment; filename="%s"', $filename),
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control'             => 'must-revalidate',
            'Pragma'                    => 'public',
            'Content-Length'            => $filesize
        );

        //return a Symfony Response
        $response = new Response($content, 200, $headers);
        $response->setCharset( $charset );
        $response->expire();

        return $response;
    }
}