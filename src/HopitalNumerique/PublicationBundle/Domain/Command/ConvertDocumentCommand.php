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
    public $file;

    /**
     * @var integer
     */
    public $publicationId;

    /**
     * ConvertDocumentCommand constructor.
     *
     * @param $publicationId
     * @param File $file
     */
    public function __construct($publicationId, $file = null)
    {
        $this->publicationId = $publicationId;
        $this->file = $file;
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
            "application/vnd.oasis.opendocument.text",
            "application/wps-office.docx",
        ];

        $extensions = [
            'docx',
            'odt',
        ];

        if (in_array($this->file->getMimeType(), $mimeTypes)) {
            return true;
        }

        if (in_array($this->file->getClientOriginalExtension(), $extensions)) {
            return true;
        }

        $context->buildViolation('Please upload a valid document')
            ->atPath('file')
            ->addViolation();

        return false;
    }
}
