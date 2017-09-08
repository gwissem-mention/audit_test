<?php

namespace HopitalNumerique\ContextualNavigationBundle\Twig;

use HopitalNumerique\CoreBundle\DependencyInjection\Entity;

class HelpBlockExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Environment $twig
     */
    protected $twig;

    /**
     * @var Entity $entity
     */
    protected $entity;

    /**
     * HelpBlockExtension constructor.
     *
     * @param \Twig_Environment $twig
     * @param Entity $entity
     */
    public function __construct(\Twig_Environment $twig, Entity $entity)
    {
        $this->twig = $twig;
        $this->entity = $entity;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('contextualNavigationHelpBlock', [$this, 'getContextualNavigationHelpBlock'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Return HTML footer help block
     *
     * @param $object
     *
     * @return string
     */
    public function getContextualNavigationHelpBlock($object)
    {
        $entityType = $this->entity->getEntityType($object);
        $entityId = $this->entity->getEntityId($object);
        $entityTitle = $this->entity->getTitleByEntity($object);
        $references = $this->entity->getReferencesByEntity($object, true);

        $types = null;
        if (in_array($this->entity->getEntityType($object), [Entity::ENTITY_TYPE_OBJET, Entity::ENTITY_TYPE_CONTENU])) {
            $types = $this->entity->getCategoryByEntity($object);
        }

        return $this->twig->render(
            'HopitalNumeriqueContextualNavigationBundle:help_block:help_block.html.twig',
            [
                'entityType' => $entityType,
                'entityId' => $entityId,
                'entityTitle' => $entityTitle,
                'references' => $references,
                'types' => $types,
            ]
        );
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'contextual_navigation_twig_help_block';
    }
}
