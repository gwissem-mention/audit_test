<?php

namespace HopitalNumerique\PublicationBundle\Exception\Converter;

/**
 * Class IncorrectFormatException
 */
class IncorrectFormatException extends \Exception
{
    protected $mimeType;

    public function __construct($mimeType)
    {
        parent::__construct(sprintf('File with %s mimetype is not supported', $mimeType));
    }

    /**
     * @return mixed
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }
}
