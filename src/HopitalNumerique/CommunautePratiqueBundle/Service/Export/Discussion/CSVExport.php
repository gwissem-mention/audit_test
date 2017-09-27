<?php

namespace HopitalNumerique\CommunautePratiqueBundle\Service\Export\Discussion;

use HopitalNumerique\CommunautePratiqueBundle\Entity\Discussion\Discussion;
use Symfony\Component\Translation\TranslatorInterface;

class CSVExport
{
    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * CSVExport constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function export(Discussion $discussion)
    {
        $file = tmpfile();

        fputcsv($file, $this->getHeader());

        foreach ($discussion->getMessages() as $message) {
            fputcsv($file, [
                $message->getUser()->getPrenomNom(),
                $message->getCreatedAt()->format('d/m/Y'),
                $message->getContent(),
            ]);
        }

        return file_get_contents(stream_get_meta_data($file)['uri']);
    }

    protected function getHeader()
    {
        return [
            $this->translator->trans('discussion.export.header.author', [], 'cdp_discussion'),
            $this->translator->trans('discussion.export.header.creation_date', [], 'cdp_discussion'),
            $this->translator->trans('discussion.export.header.message', [], 'cdp_discussion'),
        ];
    }
}
