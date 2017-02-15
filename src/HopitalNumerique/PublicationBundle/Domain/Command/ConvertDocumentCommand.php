<?php

namespace HopitalNumerique\PublicationBundle\Domain\Command;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Take a document file and convert it to structured tree
 *
 * @package HopitalNumerique\PublicationBundle\Domain\Command
 */
class ConvertDocumentCommand
{
    /**
     * @var UploadedFile
     *
     * @Assert\NotNull()
     * @Assert\File(
     *     maxSize = "100M",
     *     mimeTypesMessage = "Please upload a valid document"
     * )
     */
    public $document;

    /**
     * @var integer
     */
    public $infradocId;

    /**
     * ConvertDocumentCommand constructor.
     *
     * @param $infradocId
     * @param File $document
     */
    public function __construct($infradocId, $document = null)
    {
        $this->infradocId = $infradocId;
        $this->document = $document;
    }

    /**
     * @Assert\Callback()
     * @param ExecutionContextInterface $context
     *
     * @return bool
     */
    public function validate(ExecutionContextInterface $context)
    {
        $mimeTypes = [
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "application/msword",
            "application/vnd.oasis.opendocument.text",
            "application/wps-office.docx"
        ];

        $extensions = [
            'doc',
            'docx'
        ];

        if (in_array($this->document->getMimeType(), $mimeTypes)) {
            return true;
        }

        if (in_array($this->document->getClientOriginalExtension(), $extensions)) {
            return true;
        }

        $context->buildViolation('Please upload a valid document')
            ->atPath('document')
            ->addViolation();

        return false;
    }
}
