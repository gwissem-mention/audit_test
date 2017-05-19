<?php

namespace CCDNComponent\BBCodeBundle\Tag\Format;

use CCDNComponent\BBCode\Node\Lexeme\Tag\Format\Link;

class ExternalLink extends Link
{
    /**
     *
     * @var string $canonicalLexemeName
     */
    protected static $canonicalLexemeName = 'ExternalLink';

    /**
     *
     * @var string $canonicalTokenName
     */
    protected static $canonicalTokenName = 'LINK';

    /**
     *
     * @var string $canonicalGroupName
     */
    protected static $canonicalGroupName = 'Format';

    /**
     *
     * @var string $buttonLabel
     */
    protected static $buttonLabel = 'Add target blank Link';

    /**
     *
     * @var string $buttonIcon
     */
    protected static $buttonIcon = 'glyphicon glyphicon-link';

    /**
     *
     * Regular expressions to match against the
     * scan chunk during lexing process. The order
     * must match the $lexingHtml variable.
     *
     * @var array $lexingPattern
     */
    protected static $lexingPattern = array('/^\[LINK?(\=(.*?)*)\]$/', '/^\[\/LINK\]$/');

    /**
     *
     * HTML to output at the index of the matching regular
     * expression found in the $lexingPattern variable.
     *
     * Indexes between $lexingPattern and $lexingHtml must match.
     *
     * @var array $lexingHtml
     */
    protected static $lexingHtml = array('<a href="{{ param[0] }}" target="_blank">', '</a>');

    /**
     *
     * @var string $buttonGroup
     */
    protected static $buttonGroup = array();

    /**
     *
     * Question for BBCode Editor to prompt user for tag parameter.
     */
    protected static $buttonParameterQuestion = "Enter target blank URL";
}
