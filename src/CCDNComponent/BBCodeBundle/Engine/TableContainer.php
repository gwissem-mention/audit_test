<?php

namespace CCDNComponent\BBCodeBundle\Engine;

class TableContainer extends \CCDNComponent\BBCode\Engine\Table\TableContainer
{
    protected $lexemeClasses = array(
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Asset\Image',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Asset\Vimeo',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Asset\Youtube',

        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Block\Code',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Block\CodeGroup',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Block\Quote',

        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Format\Bold',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Format\Heading1',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Format\Heading2',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Format\Heading3',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Format\Italic',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Format\Link',
        '\CCDNComponent\BBCodeBundle\Tag\Format\ExternalLink',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Format\ListItem',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Format\ListOrdered',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Format\ListUnordered',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Format\Strike',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Format\SubScript',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Format\SuperScript',
        '\CCDNComponent\BBCode\Node\Lexeme\Tag\Format\Underline',
    );
}
