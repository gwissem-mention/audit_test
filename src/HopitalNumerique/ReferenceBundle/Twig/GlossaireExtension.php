<?php
namespace HopitalNumerique\ReferenceBundle\Twig;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\ReferenceBundle\Doctrine\Glossaire\Reader as GlossaireReader;
use Symfony\Component\Routing\RouterInterface;

class GlossaireExtension extends \Twig_Extension
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface Router
     */
    private $router;

    /**
     * @var \HopitalNumerique\ReferenceBundle\Doctrine\Glossaire\Reader GlossaireReader
     */
    private $glossaireReader;

    /**
     * @var \HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine CurrentDomaine
     */
    private $currentDomaine;


    /**
     * Constructeur.
     */
    public function __construct(RouterInterface $router, GlossaireReader $glossaireReader, CurrentDomaine $currentDomaine)
    {
        $this->router = $router;
        $this->glossaireReader = $glossaireReader;
        $this->currentDomaine = $currentDomaine;
    }


    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            'glossaire_add' => new \Twig_Filter_Method($this, 'add')
        ];
    }

  
    /**
     * Vérifie que l'user à bien l'accès à la route
     *
     * @param array $options Tableau d'options
     *
     * @return boolean
     */
    public function add($text, $entity)
    {
        $glossaireReferences = $this->glossaireReader->getGlossaireReferencesByEntityAndDomaine($entity, $this->currentDomaine->get());

        if (count($glossaireReferences) > 0) {
            $text = $this->convertBadPortionsToAsciiHtml($text);

            foreach ($glossaireReferences as $glossaireReference) {
                $wordSearchPattern = '/[\;\<\>\,\"\(\)\'\& ]{1,1}'.$glossaireReference->getSigleHtmlForGlossaire().'[\;\<\>\,\"\(\)\'\.\& ]{1,1}/'.($glossaireReference->isCasseSensible() ? '' : 'i');
                preg_match_all($wordSearchPattern, $text, $wordSearchPatternMatches);

                foreach ($wordSearchPatternMatches[0] as $wordSearchPatternMatch) {
                    $html =
                        '¬<a class="fancybox fancybox.ajax" href="'.$this->router->generate('hopitalnumerique_reference_glossaire_popin', ['glossaireReference' => $glossaireReference->getId()]).'¬"><acronym class="glosstool" data-html="true" title="¬'.
                        (('' != $glossaireReference->getDescriptionCourte()) ? $this->convertToAsciiHtml($glossaireReference->getDescriptionCourte())  : '').
                        '¬">¬' . $this->convertToAsciiHtml(substr($wordSearchPatternMatch, 1, -1)) . '</acronym></a>'
                    ;

                    $html = substr($wordSearchPatternMatch, 0, 1) . $html . substr($wordSearchPatternMatch, -1);
                    $text = str_replace($wordSearchPatternMatch, $html, $text);
                }
            }

            $text = str_replace('¬', '', $text);
        }

        return $text;
    }

    /**
     * Convertir les morceaux de texte ne devant pas contenir du glossaire en ASCII.
     *
     * @return string
     */
    private function convertBadPortionsToAsciiHtml($text)
    {
        $noPattern = '/(<a(.*)<\/a>)|(<img.*\/>)/iU';
        preg_match_all($noPattern, $text, $noMatches);
    
        foreach ($noMatches[0] as $match) {
            $text = str_replace($match, $this->convertToAsciiHtml($match), $text);
        }

        return $text;
    }

    /**
     * Convertir une chaîne en HTML ASCII.
     *
     * @param string $text Texte
     * @return string Texte ASCII
     */
    private function convertToAsciiHtml($text)
    {
        if ('' != $text) {
            $textDecode = utf8_decode(str_replace(
                ['œ', "’"],
                ['oe', "'"],
                $text
            ));

            $text = '';
            foreach (str_split($textDecode) as $obj) {
                $text .= '&#'.ord($obj).';';
            }
        }

        return $text;
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'reference_glossaire_extension';
    }
}
