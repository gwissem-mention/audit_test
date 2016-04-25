<?php
namespace Nodevo\MenuBundle\Twig;

use Nodevo\MenuBundle\DependencyInjection\MenuCache;
use Knp\Menu\Twig\Helper;

/**
 * {@inheritdoc}
 */
class MenuExtension extends \Knp\Menu\Twig\MenuExtension
{
    /**
     * @var \Knp\Menu\Twig\Helper TwigHelper
     */
    private $knpMenuTwigHelper;

    /**
     * @var \Nodevo\MenuBundle\DependencyInjection\MenuCache MenuCache
     */
    private $menuCache;


    /**
     * Constructeur.
     */
    public function __construct(Helper $knpMenuTwigHelper, MenuCache $menuCache)
    {
        parent::__construct($knpMenuTwigHelper);
        $this->knpMenuTwigHelper = $knpMenuTwigHelper;
        $this->menuCache = $menuCache;
    }


    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'hn_menu_get' => new \Twig_Function_Method($this, 'get'),
            'hn_menu_render' => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html')))
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function get($menu, array $path = array(), array $options = array())
    {
        return parent::get($menu, $path, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function render($menu, array $options = array(), $renderer = null)
    {
        if (is_string($menu)) {
            $menuCacheId = $this->menuCache->getMenuCacheLabel($menu, $options);

            if ($this->menuCache->hasRender($menuCacheId)) {
                return $this->menuCache->getRender($menuCacheId);
            } else {
                $menuRender = parent::render($menu, $options, $renderer);
                $this->menuCache->setRender($menuCacheId, $menuRender);
                return $menuRender;
            }
        }

        return parent::render($menu, $options, $renderer);
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hn_menu';
    }
}
