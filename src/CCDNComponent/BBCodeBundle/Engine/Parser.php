<?php

/*
 * This file is part of the CCDNComponent BBCode
 *
 * (c) CCDN (c) CodeConsortium <http://www.codeconsortium.com/>
 *
 * Available on github <http://www.github.com/codeconsortium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CCDNComponent\BBCodeBundle\Engine;

/**
 *
 * @category CCDNComponent
 * @package  BBCode
 *
 * @author   Reece Fowell <reece@codeconsortium.com>
 * @license  http://opensource.org/licenses/MIT MIT
 * @version  Release: 2.0
 * @link     https://github.com/codeconsortium/CCDNComponentBBCode
 *
 */
class Parser
{
    /**
     *
     * @access public
     * @param  array  $tree
     * @return string
     */
    public function process($tree)
    {
        $html = $this->parse($tree);

        return '<div class="bb_wrapper">' . nl2br($html) . '</div>';
    }

    /**
     *
     * @access protected
     * @param  array  $tree
     * @return string
     */
    protected function parse($tree)
    {
        return $tree->cascadeRender();
    }
}
