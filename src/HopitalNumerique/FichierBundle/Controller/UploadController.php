<?php

namespace HopitalNumerique\FichierBundle\Controller;

use HopitalNumerique\FichierBundle\Service\FilePathFinder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use HopitalNumerique\FichierBundle\Entity\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use HopitalNumerique\FichierBundle\Domain\Command\UploadFileHandler;
use HopitalNumerique\FichierBundle\Domain\Command\UploadFileCommand;

class UploadController extends Controller
{
    /**
     * @param Request $request
     *
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     *
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');

        $acceptedFilesExtension = explode(',', $this->container->getParameter('nodevo_gestionnaire_media.moxie_manager.extensions_autorisees'));

        if (!in_array($uploadedFile->getClientOriginalExtension(), $acceptedFilesExtension)) {
            return new JsonResponse(
                $this->get('translator')->trans('upload.error.type', ['%ext%' => $this->getParameter('nodevo_gestionnaire_media.moxie_manager.extensions_autorisees')]),
                418
            );
        }

        if ($uploadedFile->getSize() > (10 * 1000000)) {
            return new JsonResponse(
                $this->get('translator')->trans('upload.error.size'),
                418
            );
        }

        /** @var File $file */
        $file = $this->get(UploadFileHandler::class)->handle(new UploadFileCommand($uploadedFile, $this->getUser()));

        return new JsonResponse([
            'fileId' => $file->getId(),
            'name' => $uploadedFile->getClientOriginalName(),
        ]);
    }

    /**
     * @param File $file
     *
     * @Security("is_granted('view', file)")
     *
     * @return BinaryFileResponse
     */
    public function previewAction(File $file)
    {
        return new BinaryFileResponse($this->get(FilePathFinder::class)->getFilePath($file));
    }

    /**
     * @param File $file
     *
     * @Security("is_granted('delete', file)")
     *
     * @return JsonResponse
     */
    public function removeAction(File $file)
    {
        unlink($this->get(FilePathFinder::class)->getFilePath($file));

        $this->getDoctrine()->getManager()->remove($file);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse();
    }
}
