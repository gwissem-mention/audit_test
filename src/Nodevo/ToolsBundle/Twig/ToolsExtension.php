<?php

namespace Nodevo\ToolsBundle\Twig;

use Nodevo\ToolsBundle\Tools\Chaine;

class ToolsExtension extends \Twig_Extension
{
    /**
     * Retourne la liste des filtres custom pour cette extension.
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            'minifieMoi' => new \Twig_Filter_Method($this, 'minifie'),
            'base64Nodevo' => new \Twig_Filter_Method($this, 'base64Nodevo'),
            'tinyUrl' => new \Twig_Filter_Method($this, 'tinyUrl'),
            'bitly' => new \Twig_Filter_Method($this, 'bitly'),
            'unescape' => new \Twig_Filter_Method($this, 'unescape', ['is_safe' => ['html']]),
        ];
    }

    /**
     * Retourne une chaine de caractère minifiée.
     *
     * @param string $string              Chaine de caractère à minifier
     * @param string $caractereSeparateur
     * @param bool   $toutEnMinuscule
     *
     * @return string
     */
    public function minifie($string, $caractereSeparateur = '-', $toutEnMinuscule = true)
    {
        $tool = new Chaine($string);
        $nomMinifier = $tool->minifie($caractereSeparateur, $toutEnMinuscule);

        return $nomMinifier;
    }

    public function base64Nodevo($string)
    {
        return str_replace(['+', '/'], ['-', '_'], base64_encode($string));
    }

    public function tinyUrl($url)
    {
        return file_get_contents('http://tinyurl.com/api-create.php?url=' . $url);
    }

    public function bitly($long_url)
    {
        $bitly_login = 'nodevo';
        $bitly_apikey = 'R_56d2df057dc448dd99f4af11763b34dd';

        $bitly_response = json_decode(file_get_contents("http://api.bit.ly/v3/shorten?login={$bitly_login}&apiKey={$bitly_apikey}&longUrl={$long_url}&format=json"));

        return isset($bitly_response->data->url) ? $bitly_response->data->url : '';
    }

    public function unescape($html)
    {
        $html = preg_replace(
            [
                '@<head[^>]*?>.*?</head>@siu',
                '@<style[^>]*?>.*?</style>@siu',
                '@<script[^>]*?.*?</script>@siu',
                '@<object[^>]*?.*?</object>@siu',
                '@<embed[^>]*?.*?</embed>@siu',
                '@<applet[^>]*?.*?</applet>@siu',
                '@<noframes[^>]*?.*?</noframes>@siu',
                '@<noscript[^>]*?.*?</noscript>@siu',
                '@<noembed[^>]*?.*?</noembed>@siu',
            ],
            [
                ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
            ],
            $html
        );

        return $html;
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services.
     *
     * @return string
     */
    public function getName()
    {
        return 'tools_extension';
    }
}
