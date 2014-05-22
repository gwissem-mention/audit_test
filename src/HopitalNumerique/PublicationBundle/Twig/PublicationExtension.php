<?php
namespace HopitalNumerique\PublicationBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * Parse le contenu pour créer les liens vers les publications
     *
     * @param string $content Contenu
     *
     * @return string
     */
    public function parsePublication($content)
    {
        $pattern = '/\[([a-zA-Z]+)\:(\d+)\;(([a-zA-Z0-9àáâãäåçèéêëìíîïðòóôõöùúûüýÿ\&\'\`\"\<\>\!\:\?\,\;\.\%\#\@\_\-\+]| )+)\;([a-zA-Z0-9]+)\]/';
        preg_match_all($pattern, $content, $matches);
         
        // matches[0] tableau des chaines completes trouvée
        // matches[1] tableau des chaines avant les : trouvé
        // matches[1] tableau des ID après les : trouvé
        if(is_array($matches[1]))
        {
            foreach($matches[1] as $key => $value)
            {
                switch($value){
                    case 'PUBLICATION':
                        //cas Objet
                        $objet  = $this->getManagerObjet()->findOneBy( array( 'id' => $matches[2][$key] ) );
                        $target = $matches[5][$key] == 1 ? 'target="_blank"' : "";
                        if($objet)
                            $replacement = '<a href="/publication/'.$matches[2][$key].'-' . $objet->getAlias() . '" '.$target.'>' . $matches[3][$key] . '</a>';
                        else
                            $replacement = "<a href=\"javascript:alert('Cette publication n\'existe pas')\" ".$target.">" . $matches[3][$key] . ' </a>';

                        $pattern = $matches[0][$key];
                        $content = str_replace($pattern, $replacement, $content);
                                
                        break;
                    case 'INFRADOC':
                        //cas contenu
                        $contenu = $this->getManagerContenu()->findOneBy( array( 'id' => $matches[2][$key] ) );
                        $target  = $matches[5][$key] == 1 ? 'target="_blank"' : "";
                        if( $contenu ){
                            $objet       = $contenu->getObjet();
                            $replacement = '<a href="/publication/'.$objet->getId().'-' . $objet->getAlias() . '/'.$matches[2][$key].'-'.$contenu->getAlias().'" '.$target.'>' . $matches[3][$key].'</a>';
                        }else
                            $replacement = "<a href=\"javascript:alert('Cet infra-doc n\'existe pas')\" ".$target.">" . $matches[3][$key].'</a>';

                        $pattern = $matches[0][$key];
                        $content = str_replace($pattern, $replacement, $content);
                        break;
                    case 'ARTICLE':
                        //cas Objet
                        $objet  = $this->getManagerObjet()->findOneBy( array( 'id' => $matches[2][$key] ) );
                        $target = $matches[5][$key] == 1 ? 'target="_blank"' : "";
                        if($objet)
                            $replacement = '<a href="/publication/article/'.$matches[2][$key].'-' . $objet->getAlias() . '" '.$target.'>' . $matches[3][$key] . '</a>';
                        else
                            $replacement = "<a href=\"javascript:alert('Cet article n\'existe pas')\" ".$target.">" . $matches[3][$key] . ' </a>';

                        $pattern = $matches[0][$key];
                        $content = str_replace($pattern, $replacement, $content);
                                
                        break;
                    case 'AUTODIAG':
                        //cas Outil
                        $outil  = $this->getManagerOutil()->findOneBy( array( 'id' => $matches[2][$key] ) );
                        $target = $matches[5][$key] == 1 ? 'target="_blank"' : "";
                        if($outil)
                            $replacement = '<a href="/autodiagnostic/'. $outil->getAlias() . '" '.$target.'>' . $matches[3][$key] . '</a>';
                        else
                            $replacement = "<a href=\"javascript:alert('Cet outil n\'existe pas')\" ".$target.">" . $matches[3][$key] . ' </a>';

                        $pattern = $matches[0][$key];
                        $content = str_replace($pattern, $replacement, $content);
                                
                        break;
                }
            }
        }
        
        return $content;
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
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'publication_extension';
    }
}