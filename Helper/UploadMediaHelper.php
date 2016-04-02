<?php
/**
 * Created by PhpStorm.
 * User: GoForBroke
 * Date: 29.03.2016
 * Time: 23:22
 */

namespace GFB\CommonBundle\Helper;


use Application\Sonata\MediaBundle\Entity\Media;
use Sonata\MediaBundle\Entity\MediaManager;
use Sonata\MediaBundle\Provider\ImageProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadMediaHelper
{
    /** @var ContainerInterface */
    private $container;

    /** @var MediaManager */
    private $mediaManager;

    /**
     * UploadMediaHelper constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->mediaManager = $this->container->get('sonata.media.manager.media');
    }

    /**
     * @param UploadedFile $file
     * @param string $context
     * @param string $provider
     * @param null $toMedia|int
     * @return Media|null
     */
    public function upload(UploadedFile $file, $context, $provider = null, $toMedia = null)
    {
        $file->move(sys_get_temp_dir(), $file->getBasename());
        if (/*!$file instanceof UploadedFile || */
        !$file->isValid()
        ) {
//            return false;
        }

        if (null == $toMedia) {
            $media = new Media();
        } else {
            $media = $this->mediaManager->find($toMedia);
        }
        $media->setBinaryContent($file);
        $media->setContext($context);
        $media->setProviderName($provider);

        if (null == $provider) {
            $imageMimeTypes = array('image/jpeg', 'image/png', 'image/gif');
            $fileMimeTypes = array(
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword',
                'application/pdf',
                'application/x-pdf',
            );
            if (in_array($file->getMimeType(), $fileMimeTypes)) {
                $media->setProviderName('sonata.media.provider.file');
            }
            if (in_array($file->getMimeType(), $imageMimeTypes)) {
                $media->setProviderName('sonata.media.provider.image');
            }
        } else {
            $media->setProviderName($provider);
        }

        $this->mediaManager->save($media);

        return $media;
    }

    /**
     * @param string $path
     * @param string $context
     * @return Media|null
     */
    public function createMediaWithImageUrl($path, $context = 'default')
    {
        try {
            $tempFile = sys_get_temp_dir() . "/" . basename($path);

            $imagick = new \Imagick($path);
            $imagick->writeImage($tempFile);

            $media = new Media();
            $media->setBinaryContent(
                new UploadedFile($tempFile, basename($tempFile), null, null, null, true)
            );
            $media->setContext($context);
            $media->setProviderName('sonata.media.provider.image');
            $this->mediaManager->save($media);

            return $media;
        } catch (\Exception $ex) {
            echo $ex->getFile() . " [" . $ex->getLine() . "] - " . $ex->getMessage() . "<br/>";
        }

        return null;
    }

    /**
     * @param Media $media
     * @param string $format
     * @return string
     */
    public function getPublicUrl(Media $media, $format = 'reference')
    {
        if (!$media) {
            return '';
        }

        /** @var ImageProvider $provider */
        $provider = $this->container->get($media->getProviderName());
        $format = $provider->getFormatName($media, $format);

        return $provider->generatePublicUrl($media, $format);
    }
}