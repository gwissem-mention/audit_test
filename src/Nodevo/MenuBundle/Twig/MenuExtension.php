<?php
namespace Nodevo\MenuBundle\Twig;

use HopitalNumerique\UserBundle\Entity\User;
use Knp\Menu\Twig\Helper;
use Nodevo\MenuBundle\DependencyInjection\MenuCache;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * {@inheritdoc}
 */
class MenuExtension extends \Knp\Menu\Twig\MenuExtension
{
    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface TokenStorage
     */
    private $tokenStorage;

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
    public function __construct(TokenStorageInterface $tokenStorage, Helper $knpMenuTwigHelper, MenuCache $menuCache)
    {
        parent::__construct($knpMenuTwigHelper);
        $this->tokenStorage = $tokenStorage;
        $this->knpMenuTwigHelper = $knpMenuTwigHelper;
        $this->menuCache = $menuCache;
    }


    /**
     * Retourne l'utilisateur connecté.
     *
     * @return \HopitalNumerique\UserBundle\Entity\User|null User
     */
    private function getUser()
    {
        return (null !== $this->tokenStorage->getToken() ? ($this->tokenStorage->getToken()->getUser() instanceof User ? $this->tokenStorage->getToken()->getUser() : null) : null);
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
            $menuCacheId = $this->menuCache->getMenuCacheLabel($menu, $options, $this->getUser());

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
