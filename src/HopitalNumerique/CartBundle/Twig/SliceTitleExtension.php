<?php

namespace HopitalNumerique\CartBundle\Twig;

class SliceTitleExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('sliceTitle', array($this, 'sliceTitle')),
        );
    }

    /**
     * Allows to change title tag levels in text.
     * Use case sample with filter 'text|sliceTitle(2, 1, 2)' : replace respectively h1, h2 by h3, h4
     * and other title tags (h3, h4, h5, h6) by <DIV class='title-X'> (with X equals to current level title.
     *
     * @param string $text Text we want to process title tag levels (text we are applying filter).
     * @param int $sliceValue Allows to define slice value for title tag level.
     * If 0 : title tag levels won't be changed (keep value).
     * If > 0 : title tag levels will be incremented (e.g. for sliceValue = 2 : h1 -> h3).
     * If < 0 : title tag levels will be decremented (e.g. for sliceValue = -1 : h5 -> h4).
     * @param int $startLevel Index of title tag level we want to start slice process.
     * NOTE : All titles tags before this level value will be replaced by DIV tag with CSS class 'title-[current level]'.
     * E.g. if $startSlice = 2, <h1> tags will be replace by <DIV class="title-1">.
     * @param int $endLevel Index of title tag level we want to end slice process.
     * NOTE : All titles tags after this value will be replaced by DIV tag with CSS class 'title-[title tag level]'.
     * E.g. if $startSlice = 5, <h6> tags will be replace by <DIV class="title-6">.
     *
     * @return string
     */
    public function sliceTitle($text, $sliceValue = 0, $startLevel = 1, $endLevel = 6)
    {
        $replaceValues = implode('', array_filter(range(1, 6), function($level) use ($startLevel, $endLevel) {
            return $level < $startLevel || $level > $endLevel;
        }));

        // Process slice title tag levels first
        $sliceRegexp = '/<(\/)?h([' . $startLevel . '-' . $endLevel . '])>/';
        $updatedText = preg_replace_callback($sliceRegexp, function ($matches) use ($sliceValue) {
            return '<' . $matches[1] . 'h' . ($matches[2] + $sliceValue) . '>';
        }, $text);

        // Process replace title tags if needed
        if (strlen($replaceValues) > 0) {
            $replaceRegexp = '/<(\/)?h([' . $replaceValues . '])>/';
            $updatedText = preg_replace_callback($replaceRegexp, function ($matches) {
                return $matches[1] ? '</div>' : '<div class="title-' . $matches[2] . '">';
            }, $updatedText);
        }

        return $updatedText;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'slice_title_extension';
    }
}
