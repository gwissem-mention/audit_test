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
            'getPublications' => new \Twig_Filter_Method($this, 'getPublications')
        );
    }

    /**
     * Récupère la liste des publications lié à cette réponse
     *
     * @param integer $id ID de la réponse
     *
     * @return string
     */
    public function getPublications( $id )
    {
        $question   = $this->getManagerQuestion()->findOneBy( array('id'=>$id) );
        $references = $question->getReferences();
        $refs       = array();

        foreach($references as $reference)
            $refs[] = $reference->getReference()->getId();

        //On récupère le role de l'user connecté
        $user = $this->container->get('security.context')->getToken()->getUser();
        $role = $this->container->get('nodevo_role.manager.role')->getUserRole($user);

        $objets = $this->container->get('hopitalnumerique_recherche.manager.search')->getObjetsForAutodiag( $refs, $role );

        if( count($objets) == 0 )
            return false;

        $html = '<ul>';
        foreach ($objets as $objet) {
            $href = !is_null($objet['objet']) ? $objet['objet'] . '-' . $objet['aliasO'] . '/' . $objet['id'] . '-' . $objet['aliasC'] : $objet['id'] . '-' . $objet['alias'];
            $html .= '<li><a href="/publication/'.$href.'" >'.$objet['titre'].'</a></li>';
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
     * Retourne le nom de l'extension : utilisé dans les services
     *
     * @return string
     */
    public function getName()
    {
        return 'autodiag_extension';
    }
}
