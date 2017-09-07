<?php

namespace HopitalNumerique\ReferenceBundle\Twig;

use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\ReferenceBundle\Doctrine\Glossaire\Reader as GlossaireReader;
use HopitalNumerique\ReferenceBundle\Doctrine\Glossaire\Parse as GlossaireParser;
use HopitalNumerique\ReferenceBundle\Entity\Reference;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class GlossaireExtension
 */
class GlossaireExtension extends \Twig_Extension
{
    /**
     * @var RouterInterface Router
     */
    private $router;

    /**
     * @var GlossaireReader GlossaireReader
     */
    private $glossaireReader;

    /**
     * @var GlossaireParser $glossaryParser
     */
    protected $glossaryParser;

    /**
     * @var CurrentDomaine CurrentDomaine
     */
    private $currentDomaine;

    private $badPortionsConverted = [];

    /**
     * Constructeur.
     *
     * @param RouterInterface $router
     * @param GlossaireReader $glossaireReader
     * @param CurrentDomaine  $currentDomaine
     * @param GlossaireParser $glossaryParser
     */
    public function __construct(
        RouterInterface $router,
        GlossaireReader $glossaireReader,
        CurrentDomaine $currentDomaine,
        GlossaireParser $glossaryParser
    ) {
        $this->router = $router;
        $this->glossaireReader = $glossaireReader;
        $this->currentDomaine = $currentDomaine;
        $this->glossaryParser = $glossaryParser;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            'glossaire_add' => new \Twig_Filter_Method($this, 'add'),
            'glossaireParse' => new \Twig_Filter_Method($this, 'parse'),
            'glossaire_list' => new \Twig_Filter_Method($this, 'listGlossaire'),
        ];
    }

    /**
     * Detect references in text then transform text to display acronym tag
     *
     * @param string $text
     *
     * @return string
     */
    public function parse($text)
    {
        $sigles = $this->glossaryParser->getFoundSiglesByDomainAndText($this->currentDomaine->get(), $text);

        if (count($sigles) > 0) {
            return $this->parseTextWithReferences($text, $sigles);
        }

        return $text;
    }

    /**
     * Vérifie que l'user à bien l'accès à la route.
     *
     * @param $text
     * @param $entity
     *
     * @return bool
     */
    public function add($text, $entity)
    {
        $glossaireReferences = $this->glossaireReader->getGlossaireReferencesByEntityAndDomaine(
            $entity,
            $this->currentDomaine->get()
        );
        if (count($glossaireReferences) > 0) {
            return $this->parseTextWithReferences($text, $glossaireReferences);
        }

        return $text;
    }

    /**
     * Add HTML acronym tag in text for references provided
     *
     * @param string $text
     * @param Reference[] $references
     *
     * @return string
     */
    private function parseTextWithReferences($text, $references)
    {
        $testString = [];
        $text = $this->convertBadPortionsToAsciiHtml($text);
        foreach ($references as $glossaireReference) {
            $wordSearchPattern = '/([\;\<\>\,\"\(\)\'’\& ]{1,1}|^)(' . $glossaireReference->getSigleHtmlForGlossaire() . ')([\;\<\>\,\"\(\)\'’\.\& ]{1,1}|$)/' . ($glossaireReference->isCasseSensible() ? '' : 'i');
            preg_match_all($wordSearchPattern, $text, $wordSearchPatternMatches);

            foreach ($wordSearchPatternMatches[0] as $key => $wordSearchPatternMatch) {
                if (!in_array($glossaireReference->getLibelle(), $testString)) {
                    $html =
                        '<a class="acronym fancybox fancybox.ajax" href="' . $this->router->generate(
                            'hopitalnumerique_reference_glossaire_popin',
                            ['glossaireReference' => $glossaireReference->getId()]
                        ) . '"><acronym class="glosstool" data-html="true" title="' .
                        (('' != $glossaireReference->getDescriptionCourte()) ? $this->convertToAsciiHtml(
                            $glossaireReference->getDescriptionCourte()
                        ) : '') .
                        '">' . $this->convertToAsciiHtml($wordSearchPatternMatches[2][$key]) . '</acronym></a>';

                    $html = $wordSearchPatternMatches[1][$key] . $html . $wordSearchPatternMatches[3][$key];
                    $text = $this->str_replace_first($wordSearchPatternMatch, $html, $text);
                    $testString[] = $glossaireReference->getLibelle();
                }
            }
        }

        // Revert bad portion to HTML
        foreach ($this->badPortionsConverted as $portion) {
            $text = str_replace(
                $portion,
                html_entity_decode($portion),
                $text
            );
        }

        return $text;
    }

    /**
     * @param $text
     * @param $entity
     *
     * @return array
     */
    public function listGlossaire($text, $entity)
    {
        $glossaireReferences = $this->glossaireReader->getGlossaireReferencesByEntityAndDomaine(
            $entity,
            $this->currentDomaine->get()
        );

        $list = [];
        $testString = [];
        if (count($glossaireReferences) > 0) {
            $text = $this->convertBadPortionsToAsciiHtml($text);
            foreach ($glossaireReferences as $glossaireReference) {
                $wordSearchPattern = '/[\;\<\>\,\"\(\)\'’\& ]{1,1}' . $glossaireReference->getSigleHtmlForGlossaire() . '[\;\<\>\,\"\(\)\'’\.\& ]{1,1}/' . ($glossaireReference->isCasseSensible() ? '' : 'i');
                preg_match_all($wordSearchPattern, $text, $wordSearchPatternMatches);

                foreach ($wordSearchPatternMatches[0] as $wordSearchPatternMatch) {
                    $html =
                        '<a class="acronym fancybox fancybox.ajax" href="' . $this->router->generate('hopitalnumerique_reference_glossaire_popin', ['glossaireReference' => $glossaireReference->getId()]) . '"><acronym class="glosstool" data-html="true" title="' .
                        (('' != $glossaireReference->getDescriptionCourte()) ? $this->convertToAsciiHtml($glossaireReference->getDescriptionCourte()) : '') .
                        '">' . $this->convertToAsciiHtml(substr($wordSearchPatternMatch, 1, -1)) . '</acronym></a>'
                    ;

                    $html = str_replace([',', '\'', ')', '('], '', $html);
                    if (!in_array($glossaireReference->getLibelle(), $testString)) {
                        $list[$glossaireReference->getLibelle()] = $html;
                        $testString[] = $glossaireReference->getLibelle();
                    }
                }
            }
        }

        ksort($list);

        return $list;
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
            $converted = $this->convertToAsciiHtml($match);
            $this->badPortionsConverted[] = $converted;
            $text = str_replace($match, $converted, $text);
        }

        return $text;
    }

    /**
     * Convertir une chaîne en HTML ASCII.
     *
     * @param string $text Texte
     *
     * @return string Texte ASCII
     */
    private function convertToAsciiHtml($text)
    {
        if ('' != $text) {
            $textDecode = utf8_decode(str_replace(
                ['œ', '’'],
                ['oe', "'"],
                $text
            ));

            $text = '';
            foreach (str_split($textDecode) as $obj) {
                $text .= '&#' . ord($obj) . ';';
            }
        }

        return $text;
    }

    private function str_replace_first($from, $to, $subject)
    {
        $from = '/' . preg_quote($from, '/') . '/';

        return preg_replace($from, $to, $subject, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'reference_glossaire_extension';
    }
}
