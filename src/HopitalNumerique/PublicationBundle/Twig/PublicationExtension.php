<?php
namespace HopitalNumerique\PublicationBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Nodevo\ToolsBundle\Tools\Chaine;

class PublicationExtension extends \Twig_Extension
{
    private $container;

    /**
     * Construit l'extension Twig
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Retourne la liste des filtres custom pour cette extension
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'parsePublication' => new \Twig_Filter_Method($this, 'parsePublication')
        );
    }

    /**
     * to assci
     */
    private function toascii($string)
    {
        if(!empty($string)){
            $string = str_replace('œ', 'oe', $string);
            $tempo = utf8_decode($string);
            $string = '';
            foreach (str_split($tempo) as $obj)
            {
                $string .= '&#' . ord($obj) . ';';
            }
         }
         return $string;
    }

    /**
     * Parse le contenu pour créer les liens vers les publications
     *
     * @param string $content Contenu
     *
     * @return string
     */
    public function parsePublication($content, $glossaires = false )
    {
        $pattern = '/\[([a-zA-Z]+)\:(\d+)\;(([a-zA-Z0-9àáâãäåçèéêëìíîïðòóôõöùúûüýÿ\&\'\`\"\<\>\!\:\?\,\;\.\%\#\@\_\-\+]| )*)\;([a-zA-Z0-9]+)\]/';
        preg_match_all($pattern, $content, $matches);
        
        // matches[0] tableau des chaines completes trouvée
        // matches[1] tableau des chaines avant les : trouvé
        // matches[2] tableau des ID après les : trouvé
        if(is_array($matches[1]))
        {
            foreach($matches[1] as $key => $value)
            {
                
                // Pour éviter les liens dans les liens 
                $matches[3][$key] = $this->toascii($matches[3][$key]);

                switch($value){
                    case 'PUBLICATION':
                        //cas Objet
                        $objet  = $this->getManagerObjet()->findOneBy( array( 'id' => $matches[2][$key] ) );
                        $target = $matches[5][$key] == 1 ? 'target="_blank"' : "";
                        if($objet){
                            $label = $matches[3][$key] ? $matches[3][$key] : $this->toascii($objet->getTitre());
                            $replacement = '<a href="/publication/' . $matches[2][$key] . '-' . $objet->getAlias() . '" '.$target.'>' . $label . '</a>';
                        } else {
                            $replacement = "<a href=\"javascript:alert('Cette publication n\'existe pas')\" ".$target.">" . $matches[3][$key] . ' </a>';
                        }
                        
                        $pattern = $matches[0][$key];
                        $content = str_replace($pattern, $replacement, $content);
                                
                        break;
                    case 'INFRADOC':
                        //cas contenu
                        $contenu = $this->getManagerContenu()->findOneBy( array( 'id' => $matches[2][$key] ) );
                        $target  = $matches[5][$key] == 1 ? 'target="_blank"' : "";
                        if( $contenu ){
                            $objet       = $contenu->getObjet();
                            $label       = $matches[3][$key] ? $matches[3][$key] : $this->toascii($contenu->getTitre());
                            $replacement = '<a href="/publication/'.$objet->getId().'-' . $objet->getAlias() . '/'.$matches[2][$key].'-'.$contenu->getAlias().'" '.$target.'>' . $label.'</a>';
                        } else {
                            $replacement = "<a href=\"javascript:alert('Cet infra-doc n\'existe pas')\" ".$target.">" . $matches[3][$key].'</a>';
                        }
                        
                        $pattern = $matches[0][$key];
                        $content = str_replace($pattern, $replacement, $content);
                        break;
                    case 'ARTICLE':
                        //cas Objet
                        $objet  = $this->getManagerObjet()->findOneBy( array( 'id' => $matches[2][$key] ) );
                        $target = $matches[5][$key] == 1 ? 'target="_blank"' : "";
                        if($objet){
                            $label = $matches[3][$key] ? $matches[3][$key] : $this->toascii($objet->getTitre());
                            $replacement = '<a href="/publication/article/'.$matches[2][$key].'-' . $objet->getAlias() . '" '.$target.'>' . $label . '</a>';
                        } else {
                            $replacement = "<a href=\"javascript:alert('Cet article n\'existe pas')\" ".$target.">" . $matches[3][$key] . ' </a>';
                        }
                        
                        $pattern = $matches[0][$key];
                        $content = str_replace($pattern, $replacement, $content);
                                
                        break;
                    case 'AUTODIAG':
                        //cas Outil
                        $outil  = $this->getManagerOutil()->findOneBy( array( 'id' => $matches[2][$key] ) );
                        $target = $matches[5][$key] == 1 ? 'target="_blank"' : "";
                        if($outil)
                            $replacement = '<a href="/autodiagnostic/outil/'. $outil->getId() . '-' . $outil->getAlias() .'" '.$target.'>' . $matches[3][$key] . '</a>';
                        else
                            $replacement = "<a href=\"javascript:alert('Cet outil n\'existe pas')\" ".$target.">" . $matches[3][$key] . ' </a>';

                        $pattern = $matches[0][$key];
                        $content = str_replace($pattern, $replacement, $content);
                                
                        break;
                    case 'QUESTIONNAIRE':
                        //cas Questionnaire
                        $questionnaire  = $this->getManagerQuestionnaire()->findOneBy( array( 'id' => $matches[2][$key] ) );
                        $target = $matches[5][$key] == 1 ? 'target="_blank"' : "";
                        if($questionnaire)
                        {
                            $label       = $matches[3][$key] ? $matches[3][$key] : $this->toascii($questionnaire->getNom());
                            $replacement = '<a href="/questionnaire/edit/'. $questionnaire->getId() . '" '.$target.'>' . $label . '</a>';
                        }
                        else
                        {
                            $replacement = "<a href=\"javascript:alert('Ce questionnaire n\'existe pas')\" ".$target.">" . $matches[3][$key] . ' </a>';
                        }

                        $pattern = $matches[0][$key];
                        $content = str_replace($pattern, $replacement, $content);
                                
                        break;
                }
            }
        }
        
        //Glossaire stuff
        if( $glossaires ){
            
            // Ontransforme en ASCII les texte à ne pas parser
            $noPattern = '/(<a(.*)<\/a>)|(<img.*\/>)/iU';
            preg_match_all($noPattern, $content, $noMatches);
            if( $noMatches[0] ){
                foreach($noMatches[0] as $match)
                {
                    $content = str_replace($match, $this->toascii($match), $content);
                }
            }

            $words      = $this->getManagerGlossaire()->findAll();
            $motsFounds = array();
            foreach($words as $key => $word){
                if( $word->getEtat()->getId() == 3 && in_array( trim(htmlentities($word->getMot())), $glossaires) )
                    $motsFounds[ trim(htmlentities($word->getMot())) ] = array('intitule' => $word->getIntitule(), 'sensitive' => $word->isSensitive(), 'description' => $word->getDescription());
            }

            //tri des éléments les plus longs aux plus petits
            array_multisort(
                array_map(create_function('$v', 'return strlen($v);'), array_keys($motsFounds)), SORT_DESC, 
                $motsFounds
            );
            
            foreach($motsFounds as $mot => $data ){
                
                // On converti la description en ASCII pour ne pas trouver un des mots du glossaire dans la description
                $description = $this->toascii($data['description']);

                //search word in content
                $pattern = '/[\;\<\>\,\"\(\)\'\& ]{1,1}'.$mot.'[\;\<\>\,\"\(\)\'\.\& ]{1,1}/';
                if( !$data['sensitive'] )
                    $pattern .= 'i';

                preg_match_all($pattern, $content, $matches);
                
                //when founded
                if( $matches[0] ){
                    //prepare Replacement stuff
                    $tool = new Chaine( html_entity_decode($mot) );

                    //iterate over matches
                    foreach($matches[0] as $match)
                    {

                        $html    = '¬<a target="_blank" href="/glossaire#¬'. $tool->minifie() .'¬" style="text-decoration:none"><abbr class="glosstool" data-html="true" title="¬';
                        $html .= ($data['intitule'] ? $data['intitule'] : substr($match, 1, -1) );
                        $html .= (!empty($description)) ? ' : <br>' . $description  : '';
                        $html .= '¬" >¬' . substr($match, 1, -1) . '</abbr></a>';

                        $html    = substr($match, 0, 1) . $html . substr($match, -1);
                        $content = str_replace($match, $html, $content);
                    }
                }
            }

            $content = str_replace('¬', '', $content);
        }
        
        $content = html_entity_decode($content);

        return $content;
    }

    /**
     * Retourne le manager glossaire
     *
     * @return GlossaireManager
     */
    private function getManagerGlossaire()
    {
        return $this->container->get('hopitalnumerique_glossaire.manager.glossaire'); 
    }
    
    /**
     * Retourne le manager contenu
     *
     * @return ContenuManager
     */
    private function getManagerContenu()
    {
        return $this->container->get('hopitalnumerique_objet.manager.contenu'); 
    }

    /**
     * Retourne le manager objet
     *
     * @return ObjetManager
     */
    private function getManagerObjet()
    {
        return $this->container->get('hopitalnumerique_objet.manager.objet');
    }

    /**
     * Retourne le manager outil
     *
     * @return OutilManager
     */
    private function getManagerOutil()
    {
        return $this->container->get('hopitalnumerique_autodiag.manager.outil');
    }

    /**
     * Retourne le manager questionnaire
     *
     * @return QuestionnaireManager
     */
    private function getManagerQuestionnaire()
    {
        return $this->container->get('hopitalnumerique_questionnaire.manager.questionnaire');
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'publication_extension';
    }
}
