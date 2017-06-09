<?php

namespace HopitalNumerique\ReferenceBundle\Twig;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;
use HopitalNumerique\DomaineBundle\DependencyInjection\CurrentDomaine;
use HopitalNumerique\ReferenceBundle\Doctrine\Glossaire\Reader as GlossaireReader;
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
     * @var CurrentDomaine CurrentDomaine
     */
    private $currentDomaine;

    /**
     * @var Entity $entity
     */
    protected $entity;

    private $badPortionsConverted = [];

    /**
     * @var array
     */
    private $occurrenceFounded = [];

    /**
     * Constructeur.
     *
     * @param RouterInterface $router
     * @param GlossaireReader $glossaireReader
     * @param CurrentDomaine  $currentDomaine
     * @param Entity $entity
     */
    public function __construct(
        RouterInterface $router,
        GlossaireReader $glossaireReader,
        CurrentDomaine $currentDomaine,
        Entity $entity
    ) {
        $this->router = $router;
        $this->glossaireReader = $glossaireReader;
        $this->currentDomaine = $currentDomaine;
        $this->entity = $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            'glossaire_add' => new \Twig_Filter_Method($this, 'add'),
            'glossaire_list' => new \Twig_Filter_Method($this, 'listGlossaire'),
        ];
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

        $entityType = $this->entity->getEntityType($entity);
        $entityId = $this->entity->getEntityId($entity);

        if (count($glossaireReferences) > 0) {
            $text = $this->convertBadPortionsToAsciiHtml($text);
            foreach ($glossaireReferences as $glossaireReference) {
                $wordSearchPattern = '/[\;\<\>\,\"\(\)\'’\& ]{1,1}' . $glossaireReference->getSigleHtmlForGlossaire() . '[\;\<\>\,\"\(\)\'’\.\& ]{1,1}/' . ($glossaireReference->isCasseSensible() ? '' : 'i');
                preg_match_all($wordSearchPattern, $text, $wordSearchPatternMatches);

                foreach ($wordSearchPatternMatches[0] as $wordSearchPatternMatch) {
                    if (
                        !isset($this->occurrenceFounded[$entityType]) ||
                        !isset($this->occurrenceFounded[$entityType][$entityId]) ||
                        !in_array($glossaireReference->getLibelle(), $this->occurrenceFounded[$entityType][$entityId])
                    ) {
                        $html =
                            '<a class="acronym fancybox fancybox.ajax" href="' . $this->router->generate(
                                'hopitalnumerique_reference_glossaire_popin',
                                ['glossaireReference' => $glossaireReference->getId()]
                            ) . '"><acronym class="glosstool" data-html="true" title="' .
                            (('' != $glossaireReference->getDescriptionCourte()) ? $this->convertToAsciiHtml(
                                $glossaireReference->getDescriptionCourte()
                            ) : '') .
                            '">' . $this->convertToAsciiHtml(substr($wordSearchPatternMatch, 1, -1)) . '</acronym></a>';

                        $html = substr($wordSearchPatternMatch, 0, 1) . $html . substr($wordSearchPatternMatch, -1);
                        $text = $this->str_replace_first($wordSearchPatternMatch, $html, $text);

                        $this->occurrenceFounded[$entityType][$entityId][] = $glossaireReference->getLibelle();
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
