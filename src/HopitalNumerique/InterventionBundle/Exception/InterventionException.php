<?php
namespace HopitalNumerique\InterventionBundle\Exception;

/**
 * Exception liée à une intervention.
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
class InterventionException extends \Exception
{
    /**
     * Constructeur d'une exception en base
     * 
     * @param string $message Le message expliquant l'exception
     * @param integer $code L'identifiant du code erreur
     * @param \Exception $erreurPrecedente L'exception précédente, utilisée pour le chaînage d'exception
     */
    public function __construct($message = 'Erreur intervention Hopital Numérique', $code = 0, \Exception $erreurPrecedente = null)
    {
        parent::__construct('Erreur intervention Hopital Numérique : '.$message, $code, $erreurPrecedente);
    }
}