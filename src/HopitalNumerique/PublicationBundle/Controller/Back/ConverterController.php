<?php
namespace HopitalNumerique\PublicationBundle\Controller\Back;

use Doctrine\ORM\EntityNotFoundException;
use HopitalNumerique\ObjetBundle\Entity\Objet;
use HopitalNumerique\PublicationBundle\Domain\Command\AbortDocumentCommand;
use HopitalNumerique\PublicationBundle\Domain\Command\ConvertDocumentCommand;
use HopitalNumerique\PublicationBundle\Entity\Converter\Document;
use HopitalNumerique\PublicationBundle\Form\Type\Converter\DocumentType;
use HopitalNumerique\PublicationBundle\Form\Type\Converter\UploadType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConverterController extends Controller
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $infradoc
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function uploadDocumentAction(Request $request, $infradoc)
    {
        $form = $this->createForm(UploadType::class, new ConvertDocumentCommand($infradoc), [
            'action' => $this->generateUrl('hopital_numerique_publication_converter_upload', ['infradoc' => $infradoc]),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->get('hopitalnumerique_publication.convert_document_handler')->handle(
                    $form->getData()
                );

                return $this->redirectToRoute('hopital_numerique_publication_converter_prepare', [
                    'infradoc' => $infradoc
                ]);
            }

            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }

            return new JsonResponse(
                [
                    'messages' => $errors
                ],
                400
            );
        }

        return $this->render('@HopitalNumeriquePublication/back/converter/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \HopitalNumerique\ObjetBundle\Entity\Objet $infradoc
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function prepareInfradocAction(Request $request, Objet $infradoc)
    {
        $document = $this->get('hopitalnumerique_publication.repository.document')->findByPublication($infradoc);

        if (null === $document) {
            return new JsonResponse(null, 204);
        }

        $form = $this->createForm(DocumentType::class, $document, [
            'action' => $this->generateUrl('hopital_numerique_publication_converter_prepare', [
                'infradoc' => $infradoc->getId(),
            ])
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->get('doctrine.orm.default_entity_manager');
            $em->flush();

            // Generate
            $this->get('hopitalnumerique_publication.content_generator')->generateFromDocument($document);

            return new Response();
        }

        return $this->render('@HopitalNumeriquePublication/back/converter/prepare_infradoc.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function generateAction(Document $document)
    {
        $this->get('hopitalnumerique_publication.content_generator')->generateFromDocument($document);

        return new JsonResponse();
    }

    /**
     * @param integer $publicationId
     *
     * @return JsonResponse
     */
    public function abortAction($publicationId)
    {
        $abortDocumentCommand = new AbortDocumentCommand($publicationId);

        try {
            $this->get('hopitalnumerique_publication.abort_document_handler')->handle($abortDocumentCommand);
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(null, 404);
        }

        return new JsonResponse();
    }
}
