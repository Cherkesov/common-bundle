<?php

namespace GFB\CommonBundle\Controller;

use Application\Sonata\MediaBundle\Entity\Media;
use GFB\CommonBundle\Helper\UploadMediaHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/media-file")
 * Class MediaFileController
 * @package GFB\CommonBundle\Controller
 */
class MediaFileController extends Controller
{
    /**
     * @Route("/upload", name="gfb__common__media_file__upload")
     * @param Request $request
     * @return Response
     */
    public function uploadAction(Request $request)
    {
        $files = $request->files->all();
        if (!isset($files['file'])) {
            return new JsonResponse(
                [
                    'error' => ['file is not specified'],
                ], 500
            );
        }

        $mediaId = $request->get('id');
        /** @var UploadedFile $file */
        $file = $files['file'];

        $helper = new UploadMediaHelper($this->container);
        $media = $helper->upload(
            $file,
            $request->get('context'),
            'sonata.media.provider.image',
            $mediaId
        );

        if (!$media instanceof Media) {
            return new JsonResponse(
                [
                    'error' => ['upload file unavailable'],
                ], 500
            );
        }

        return new JsonResponse(
            [
                'id' => $media->getId(),
                'path' => $helper->getPublicUrl($media, $request->get('preview_format')),
            ]
        );
    }
}
