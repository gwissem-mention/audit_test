<?php
/**
 * Classe permettant des traitements sur les chaînes de caractères
 * 
 * @author Rémi Leclerc <rleclerc@nodevo.com>
 */
namespace Nodevo\ToolsBundle\Tools;

/**
 * Classe permettant des traitements sur les chaînes de caractères
 */
class Chaine
{
    private $chaine;
    
    /**
     * Constructeur d'une chaîne de caractères.
     * 
     * @param string $chaine La chaîne à traiter
     */
    public function __construct($chaine)
    {
        $this->chaine = $chaine;
    }
    
    /**
     * Récupération de la chaine traitée
     * 
     * @return string La chaîne de caractères traitée
     */
    public function getChaine()
    {
        return $this->chaine;
    }
    
    /**
     * Set chaine traitée
     */
    public function setChaine($nouvelleChaine)
    {
        $this->chaine = $nouvelleChaine;
    }
    
    /**
     * Supprime les accents d'une chaîne de caractères.
     * 
     * @return string La chaîne sans accents
     */
    public function supprimeAccents()
    {
        $this->chaine = str_replace(
            array('À','Â','à','á','â','ã','ä','å','Ô','ò','ó','ô','õ','ö','ō','ø','È','É','Ê','è','é','ê','ë','Ç','ç','Î','ì','í','î','ï','ù','ú','û','ü','ū','ÿ','ñ'),
            array('A','A','a','a','a','a','a','a','O','o','o','o','o','o','o','o','E','E','E','e','e','e','e','C','c','I','i','i','i','i','u','u','u','u','u','y','n'),
            $this->chaine
        );

        return $this->chaine;
    }
    
    /**
     * Simplifie une chaîne de caractères (notamment pour les noms de fichiers ou les URL)
     * 
     * @param string $caractereSeparateur Le séparateur remplaçant certains caractères spéciaux comme les espaces
     * @param boolean $toutEnMinuscule VRAI ssi le résultat doit être en minuscule
     * 
     * @return string la chaîne minifiée
     */
    public function minifie($caractereSeparateur = '-', $toutEnMinuscule = true)
    {
        $this->chaine = trim($this->chaine, $caractereSeparateur);

        $this->chaine = str_replace(
            array(' ','_','-','.','\'','’','&','~','#','`','\\','/','^','@','°','+','*','$','','£','¤','%','§','<','>'),
            $caractereSeparateur,
            str_replace(
                array(',',';','?','!',':','"','{','}','(',')','[',']','«','»','='),
                '',
                str_replace(
                    array('²','œ','Œ'),
                    array('2','oe','Oe'),
                    $this->supprimeAccents()
                )
            )
        );
        if ($toutEnMinuscule)
            $this->chaine = strtolower($this->chaine);
        if ($caractereSeparateur != '')
            $this->chaine = preg_replace('/(\\'.$caractereSeparateur.')+/', $caractereSeparateur, trim($this->chaine, $caractereSeparateur));
        
        return $this->chaine;
    }
}