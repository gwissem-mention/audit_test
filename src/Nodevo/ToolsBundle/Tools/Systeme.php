<?php
namespace Nodevo\ToolsBundle\Tools;

/**
 * Classe permettant des traitements sur le système.
 */
class Systeme
{
    /**
     * Détermine la mémoire limite (en octet par défaut) qu'un script est autoriser à allouer.
     * 
     * @param string $memoryLimit Nombre d'octets de la mémoire limit, ou de méga-octets si suivi d'un "M" (ex. : "128M")
     * @return void
     */
    public static function setMemoryLimit($memoryLimit)
    {
        ini_set('memory_limit', $memoryLimit);
    }
    
    /**
     * Fixe le temps d'exécution d'un script à illimité.
     *
     * @return void
     */
    public static function setTimeLimitIllimite()
    {
        set_time_limit(0);
    }
    
    /**
     * Redirige l'internaute.
     * 
     * @param string $url URL cible
     */
    public static function redirect($url)
    {
        header('Location: '.$url);
        exit();
    }

    /**
     * Retourne la taille maximale en octet que peut avoir un fichier chargé par l'utilisateur.
     * 
     * @return integer Taille max des uploads
     */
    public static function getFileUploadMaxSize()
    {  
        return min(self::getPhpSizeInBytes(ini_get('post_max_size')), self::getPhpSizeInBytes(ini_get('upload_max_filesize')));  
    }

    /**
     * Retourne le nombre d'octets d'une taille en PHP (par exemple 2G ou 10M).
     * 
     * @return integer Taille max des uploads
     */
    public static function getPhpSizeInBytes($phpSize)
    {  
        if ( is_numeric($phpSize) ) {
            return $phpSize;
        }

        $sizeSuffixe = strtoupper(substr($phpSize, -1));
        $sizeValue = intval(substr($phpSize, 0, -1));

        switch (strtoupper($sizeSuffixe)) {
            case 'P':
                $sizeValue *= 1024;
            case 'T':
                $sizeValue *= 1024;
            case 'G':
                $sizeValue *= 1024;
            case 'M':
                $sizeValue *= 1024;
            case 'K':
                $sizeValue *= 1024;
                break;
        }

        return $sizeValue;
    }
}
