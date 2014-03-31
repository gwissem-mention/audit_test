<?php 

namespace Nodevo\ToolsBundle\Tools;

class Password
{
    public function __construct(){}

    /**
     * Génère un mot de passe de la taille $len et si besoin depuis la chaine $chaine
     *
     * @param integer $len    Taille du mot de passe
     * @param string  $chaine Chaine d'entrée pour générer le mot de passe (si nécessaire)
     *
     * @return string
     */
    public function generate($len = 8, $chaine = null)
    {
        if (is_null($chaine))
            $chaine = 'abBDEFcdefghijkmnPQRSTUVWXYpqrst23456789'; 

        $pass = '';
        srand((double)microtime()*1000000);  
        for($i=0; $i<$len; $i++) 
            $pass .= $chaine[rand()%strlen($chaine)];  
       
        return $pass;
    }

    /**
     * Fonction de cryptage
     * 
     * @param String $string La chaine à crypter
     * @param String $pass   Mot de passe de cryptage
     * 
     * @return String chaine cryptée
     */
    public function crypte($string, $pass)
    {
        srand((double)microtime()*1000000);
        $key    = md5(rand(0,32000));
        $cpt    = 0;
        $tmp    = "";
        $length = strlen($string);

        for ( $i = 0; $i < $length; $i++ ) {
            if ( $cpt == strlen($key) )
              $cpt = 0;

            $tmp.= substr($key,$cpt,1) . (substr($string,$i,1) ^ substr($key,$cpt,1) );
            $cpt++;
        }
        return base64_encode( $this->generationCle($tmp, $pass) );
    }

    /**
     * Fonction de decryptage
     * 
     * @param String $string La chaine à décrypté
     * @param String $pass   Mot de passe de décryptage
     * 
     * @return String chaine decryptée
     */
    public function decrypte($string, $pass)
    {
        $string = $this->generationCle(base64_decode($string),$pass);
        $tmp    = "";
        $length = strlen($string);

        for ( $i = 0; $i < $length; $i++ ){
            $md5 = substr($string,$i,1);
            $i++;
            $tmp.= (substr($string,$i,1) ^ $md5);
        }
        return $tmp;
    }  




    /**
     * Génère la clé de cryptage
     *
     * @param String $string Chaine
     * @param String $key    Clé
     *
     * @return String Cle
     */
    private function generationCle($string, $key)
    {
        $key    = md5($key);
        $cpt    = 0;
        $tmp    = "";
        $length = strlen($string);
        
        for ( $i = 0; $i < $length; $i++ ){
            if ( $cpt == strlen($key) )
                $cpt=0;

            $tmp.= substr($string,$i,1) ^ substr($key,$cpt,1);
            $cpt++;
        }
        return $tmp;
    }
}