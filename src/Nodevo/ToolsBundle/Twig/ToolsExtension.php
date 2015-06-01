<?php
namespace Nodevo\ToolsBundle\Twig;

use Nodevo\ToolsBundle\Tools\Chaine;

class ToolsExtension extends \Twig_Extension
{
    /**
     * Retourne la liste des filtres custom pour cette extension
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'minifieMoi'   => new \Twig_Filter_Method($this, 'minifie'),
            'base64Nodevo' => new \Twig_Filter_Method($this, 'base64Nodevo'),
            'tinyUrl'      => new \Twig_Filter_Method($this, 'tinyUrl'),
            'bitly'        => new \Twig_Filter_Method($this, 'bitly')
        );
    }

    /**
     * Retourne une chaine de caractère minifiée
     *
     * @param string  $string               Chaine de caractère à minifier
     * @param string  $caractereSeparateur
     * @param boolean $toutEnMinuscule
     *
     * @return string
     */
    public function minifie( $string, $caractereSeparateur = '-', $toutEnMinuscule = true )
    {
        $tool = new Chaine( $string );
        $nomMinifier = $tool->minifie($caractereSeparateur, $toutEnMinuscule);

        return $nomMinifier;
    }
    
    public function base64Nodevo( $string )
    {
        return str_replace(array('+', '/'), array('-', '_'), base64_encode($string));
    }

    public function tinyUrl($url) 
    {
        return file_get_contents("http://tinyurl.com/api-create.php?url=".$url);
    }

    public function bitly($long_url)
    {
        $bitly_login = 'nodevo';
        $bitly_apikey = 'R_56d2df057dc448dd99f4af11763b34dd';

        $bitly_response = json_decode(file_get_contents("http://api.bit.ly/v3/shorten?login={$bitly_login}&apiKey={$bitly_apikey}&longUrl={$long_url}&format=json"));

        return $bitly_response->data->url;
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'tools_extension';
    }
}