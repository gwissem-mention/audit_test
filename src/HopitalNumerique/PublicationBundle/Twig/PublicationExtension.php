<?php

namespace HopitalNumerique\PublicationBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class PublicationExtension extends \Twig_Extension
{
    private $container;

    /**
     * Construit l'extension Twig.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Retourne la liste des filtres custom pour cette extension.
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            'parsePublication' => new \Twig_Filter_Method($this, 'parsePublication'),
            'alignBreadcrumbs' => new \Twig_Filter_Method($this, 'alignBreadcrumbs'),
        ];
    }

    public function alignBreadcrumbs($breadcrumbs)
    {
        $indexBreadcrumbs = explode(' ', $breadcrumbs);

        $margin = 15 * (substr_count($indexBreadcrumbs[0], '.') - 1);

        return $margin;
    }

    /**
     * Parse le contenu pour créer les liens vers les publications.
     *
     * @param string $content Contenu
     *
     * @return string
     */
    public function parsePublication($content, $glossaires = false)
    {
        $content = $this->replaceInvalidCharacters($content);
        $pattern = '/\[([a-zA-Z]+)\:(\d+)\;((.*?)*)\;([a-zA-Z0-9]*)\]/';
        preg_match_all($pattern, $content, $matches);

        // matches[0] tableau des chaines completes trouvée
        // matches[1] tableau des chaines avant les : trouvé
        // matches[2] tableau des ID après les : trouvé
        if (is_array($matches[1])) {
            foreach ($matches[1] as $key => $value) {
                // Pour éviter les liens dans les liens
                $matches[3][$key] = $this->toascii($matches[3][$key]);

                switch ($value) {
                    case 'PUBLICATION':
                        //cas Objet
                        $objet = $this->getManagerObjet()->findOneBy(['id' => $matches[2][$key]]);
                        if ($matches[5][$key] == 1) {
                            $target = 'target="_blank"';
                        } elseif ($matches[5][$key] == 2) {
                            $target = 'target="_parent"';
                        } else {
                            $target = '';
                        }
                        if ($objet) {
                            $label = $matches[3][$key] ? $matches[3][$key] : $this->toascii($objet->getTitre());
                            $replacement = '<a href="/publication/' . $matches[2][$key] . '-' . $objet->getAlias() . '" ' . $target . '>' . $label . '</a>';
                        } else {
                            $replacement = "<a href=\"javascript:alert('Cette publication n\'existe pas')\" " . $target . '>' . $matches[3][$key] . ' </a>';
                        }

                        $pattern = $matches[0][$key];
                        $content = str_replace($pattern, $replacement, $content);

                        break;
                    case 'INFRADOC':
                        //cas contenu
                        $contenu = $this->getManagerContenu()->findOneBy(['id' => $matches[2][$key]]);
                        if ($matches[5][$key] == 1) {
                            $target = 'target="_blank"';
                        } elseif ($matches[5][$key] == 2) {
                            $target = 'target="_parent"';
                        } else {
                            $target = '';
                        }
                        if ($contenu) {
                            $objet = $contenu->getObjet();
                            $label = $matches[3][$key] ? $matches[3][$key] : $this->toascii($contenu->getTitre());
                            $replacement = '<a href="/publication/' . $objet->getId() . '-' . $objet->getAlias() . '/' . $matches[2][$key] . '-' . $contenu->getAlias() . '" ' . $target . '>' . $label . '</a>';
                        } else {
                            $replacement = "<a href=\"javascript:alert('Cet infra-doc n\'existe pas')\" " . $target . '>' . $matches[3][$key] . '</a>';
                        }

                        $pattern = $matches[0][$key];
                        $content = str_replace($pattern, $replacement, $content);
                        break;
                    case 'ARTICLE':
                        //cas Objet
                        $objet = $this->getManagerObjet()->findOneBy(['id' => $matches[2][$key]]);
                        if ($matches[5][$key] == 1) {
                            $target = 'target="_blank"';
                        } elseif ($matches[5][$key] == 2) {
                            $target = 'target="_parent"';
                        } else {
                            $target = '';
                        }
                        if ($objet) {
                            $label = $matches[3][$key] ? $matches[3][$key] : $this->toascii($objet->getTitre());
                            $replacement = '<a href="/publication/article/' . $matches[2][$key] . '-' . $objet->getAlias() . '" ' . $target . '>' . $label . '</a>';
                        } else {
                            $replacement = "<a href=\"javascript:alert('Cet article n\'existe pas')\" " . $target . '>' . $matches[3][$key] . ' </a>';
                        }

                        $pattern = $matches[0][$key];
                        $content = str_replace($pattern, $replacement, $content);

                        break;
                    case 'AUTODIAG':
                        //cas Outil
                        $outil = $this->getManagerOutil()->findOneBy(['id' => $matches[2][$key]]);
                        if ($matches[5][$key] == 1) {
                            $target = 'target="_blank"';
                        } elseif ($matches[5][$key] == 2) {
                            $target = 'target="_parent"';
                        } else {
                            $target = '';
                        }

                        $title = $matches[3][$key] ? $matches[3][$key] : ($outil ? $outil->getTitle() : 'Autodiag'); // Last condition if autodiag is deleted

                        if ($outil) {
                            $replacement = '<a href="/autodiagnostic/' . $outil->getId() . '" ' . $target . '>' . $title . '</a>';
                        } else {
                            $replacement = "<a href=\"javascript:alert('Cet outil n\'existe pas')\" " . $target . '>' . $title . ' </a>';
                        }

                        $pattern = $matches[0][$key];
                        $content = str_replace($pattern, $replacement, $content);

                        break;
                    case 'QUESTIONNAIRE':
                        //cas Questionnaire
                        $questionnaire = $this->getManagerQuestionnaire()->findOneBy(['id' => $matches[2][$key]]);
                        if ($matches[5][$key] == 1) {
                            $target = 'target="_blank"';
                        } elseif ($matches[5][$key] == 2) {
                            $target = 'target="_parent"';
                        } else {
                            $target = '';
                        }

                        if ($questionnaire) {
                            $label = $matches[3][$key] ? $matches[3][$key] : $this->toascii($questionnaire->getNom());
                            $replacement = '<a href="/questionnaire/edit/' . $questionnaire->getId() . '" ' . $target . '>' . $label . '</a>';
                        } else {
                            $replacement = "<a href=\"javascript:alert('Ce questionnaire n\'existe pas')\" " . $target . '>' . $matches[3][$key] . ' </a>';
                        }

                        $pattern = $matches[0][$key];
                        $content = str_replace($pattern, $replacement, $content);

                        break;
                    case 'RECHERCHEAIDEE':
                        //cas Recherche aidée
                        $rechercheAidee = $this->getManagerGestionnaireRechercheAidee()->findOneBy(['id' => $matches[2][$key]]);

                        if ($rechercheAidee) {
                            $url = $this->container->get('router')->generate('hopital_numerique_expbesoin_recherche_tinyMCE', ['id' => $rechercheAidee->getId()]);
                            $replacement = '<iframe onLoad="calcHeightIframe();" id="iframe-recherche-tinymce" frameborder=0 src="' . $url . '" width="100%" scrolling="no" height="100%"><p>Votre navigateur ne supporte pas l\'élément iframe</p></iframe>';
                        } else {
                            $replacement = '<span class="label label-danger">Un problème dans l\'affichage de la recherche aidée est survenue.</span>';
                        }

                        $pattern = $matches[0][$key];
                        $content = str_replace($pattern, $replacement, $content);

                        break;
                    case 'RECHERCHETEXTE':
                           //cas Recherche texte
                           $replacement = "<input type='texte' id=\"recherche-texte-generate\" /><button type='button' id=\"search-header-home-generate\">Rechercher</button>";
                        $pattern = $matches[0][$key];
                           $content = str_replace($pattern, $replacement, $content);

                           break;
                }
            }
        }

        $content = html_entity_decode($content);
        //Remplace un caractère qui n'est pas un espace mais un 'caractère vide' en
        $content = strtr($content, [' ' => ' ']);

        return $content;
    }

    /**
     * to assci.
     */
    private function toascii($string)
    {
        if (!empty($string)) {
            $string = str_replace('œ', 'oe', $string);
            $string = str_replace('’', "'", $string);
            $tempo = utf8_decode($string);
            $string = '';
            foreach (str_split($tempo) as $obj) {
                $string .= '&#' . ord($obj) . ';';
            }
        }

        return $string;
    }

    /**
     * Retourne le manager glossaire.
     *
     * @return GlossaireManager
     */
    private function getManagerGlossaire()
    {
        return $this->container->get('hopitalnumerique_glossaire.manager.glossaire');
    }

    /**
     * Retourne le manager contenu.
     *
     * @return ContenuManager
     */
    private function getManagerContenu()
    {
        return $this->container->get('hopitalnumerique_objet.manager.contenu');
    }

    /**
     * Retourne le manager objet.
     *
     * @return ObjetManager
     */
    private function getManagerObjet()
    {
        return $this->container->get('hopitalnumerique_objet.manager.objet');
    }

    /**
     * Retourne le manager outil.
     *
     * @return OutilManager
     */
    private function getManagerOutil()
    {
        return $this->container->get('autodiag.repository.autodiag');
    }

    /**
     * Retourne le manager questionnaire.
     *
     * @return QuestionnaireManager
     */
    private function getManagerQuestionnaire()
    {
        return $this->container->get('hopitalnumerique_questionnaire.manager.questionnaire');
    }

    /**
     * Retourne le manager gestionnaire recherche aidee.
     *
     * @return ExpBesoinGestionManager
     */
    private function getManagerGestionnaireRechercheAidee()
    {
        return $this->container->get('hopitalnumerique_recherche.manager.expbesoingestion');
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services.
     *
     * @return string
     */
    public function getName()
    {
        return 'publication_extension';
    }

    /**
     * @param $text
     *
     * @return string
     */
    private function replaceInvalidCharacters($text)
    {
        $invalid = [
            '–' => '-',
        ];

        return str_replace(array_keys($invalid), array_values($invalid), $text);
    }
}
