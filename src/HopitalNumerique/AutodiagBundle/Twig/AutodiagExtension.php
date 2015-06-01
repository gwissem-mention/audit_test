<?php

namespace HopitalNumerique\AutodiagBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AutodiagExtension extends \Twig_Extension
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
            'getPublicationsForQuestion' => new \Twig_Filter_Method($this, 'getPublicationsForQuestion'),
            'getPublicationsForChapitre' => new \Twig_Filter_Method($this, 'getPublicationsForChapitre'),
            'checkNC' => new \Twig_Filter_Method($this, 'checkNC')
        );
    }

    /**
     * Récupère la liste des publications lié à cette question
     *
     * @param integer $id ID de la question
     *
     * @return string
     */
    public function getPublicationsForQuestion( $id, $pdf = false )
    {
        $question   = $this->getManagerQuestion()->findOneBy( array('id'=>$id) );
        
        return $this->buildRefs( $question->getReferences(), $pdf );
    }

    /**
     * Récupère la liste des publications lié à ce chapitre
     *
     * @param integer $id ID du chapitre
     *
     * @return string
     */
    public function getPublicationsForChapitre( $id, $pdf = false )
    {
        $chapitre = $this->getManagerChapitre()->findOneBy( array('id'=>$id) );
        
        return $this->buildRefs( $chapitre->getReferences(), $pdf , true );
    }

    public function checkNC( $value )
    {
        return ($value !== 'NC' && '' !== $value);
    }








    /**
     * Construit les références
     *
     * @param array $references Les références
     *
     * @return string
     */
    private function buildRefs( $references, $pdf = false, $primary = false )
    {
        $refs = array();

        $domaineId = $this->container->isScopeActive('request') ? $this->container->get('request')->getSession()->get('domaineId') : null;

        foreach($references as $reference)
        {
            $refs[] = $reference->getReference()->getId();
        }

        //On récupère le role de l'user connecté
        $user = $this->container->get('security.context')->getToken()->getUser();
        $role = $this->container->get('nodevo_role.manager.role')->getUserRole($user);

        $objets = $this->container->get('hopitalnumerique_recherche.manager.search')->getObjetsForAutodiag( $domaineId, $refs, $role );

        if( count($objets) == 0 )
            return false;

        $html = '<ul>';
        foreach ($objets as $objet)
        {
            if($objet['primary'] >= 1)
            {
                $href = !is_null($objet['objet']) ? $objet['objet'] . '-' . $objet['primary'] . '-' . $objet['aliasO'] . '/' . $objet['id'] . '-' . $objet['aliasC'] : $objet['id'] . '-' . $objet['alias'];
                $html .= '<li><a href="/publication/'.$href.'" >' . ($pdf ? '/publication/' . $href . ' - ' : '' ) . $objet['titre'] . '</a></li>';
            }
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * Retourne le manager Question
     *
     * @return QuestionManager
     */
    private function getManagerQuestion()
    {
        return $this->container->get('hopitalnumerique_autodiag.manager.question');
    }

    /**
     * Retourne le manager Chapitre
     *
     * @return ChapitreManager
     */
    private function getManagerChapitre()
    {
        return $this->container->get('hopitalnumerique_autodiag.manager.chapitre');
    }

    /**
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'autodiag_extension';
    }
}
