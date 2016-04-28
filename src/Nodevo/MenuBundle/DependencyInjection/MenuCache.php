<?php
namespace Nodevo\MenuBundle\DependencyInjection;

use HopitalNumerique\UserBundle\Entity\User;

/**
 * Gestion du cache du menu.
 */
class MenuCache
{
    /**
     * @var string Préfix du nom du cache pour le rendu
     */
    const RENDER_PREFIX = 'hnmenurender_';


    /**
     * Retourne le label du cache du menu.
     *
     * @param string $menuAlias   Alias du menu
     * @param array  $menuOptions Options du menu
     * @return string Label du cache
     */
    public function getMenuCacheLabel($menuAlias, array $menuOptions = [], User $user = null)
    {
        $userRole = (null !== $user ? '_'.$user->getRole() : '');

        return self::RENDER_PREFIX.$menuAlias.(array_key_exists('template', $menuOptions) ? $menuOptions['template'] : '').$userRole;
    }

    /**
     * Retourne si le rendu est dans le cache.
     *
     * @param string $menuCacheLabel Label du cache du menu
     * @return string|null Rendu
     */
    public function hasRender($menuCacheLabel)
    {
        return (apc_exists($menuCacheLabel));
    }

    /**
     * Retourne le rendu du cache.
     *
     * @param string $menuCacheLabel Label du cache du menu
     * @return string|null Rendu
     */
    public function getRender($menuCacheLabel)
    {
        if ($this->hasRender($menuCacheLabel)) {
            return apc_fetch($menuCacheLabel);
        }

        return null;
    }

    /**
     * Met en cache le rendu du menu.
     *
     * @param string $menuCacheLabel Label du cache du menu
     */
    public function setRender($menuCacheLabel, $menuRender)
    {
        apc_store($menuCacheLabel, $menuRender);
    }

    /**
     * Supprime le cache de rendu du menu.
     *
     * @param string $menuAlias Alias du menu
     */
    public function deleteRenderByAlias($menuAlias)
    {
        $cacheLabel = $this->getMenuCacheLabel($menuAlias);
        $cacheInformations = apc_cache_info();
        $caches = $cacheInformations['cache_list'];

        foreach ($caches as $cache) {
            if ($cacheLabel === substr($cache['key'], 0, strlen($cacheLabel))) {
                apc_delete($cache['key']);
            }
        }
    }
}
