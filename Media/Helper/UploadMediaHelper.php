<?php
namespace GFB\CommonBundle\Media\Helper;

use Application\Sonata\MediaBundle\Entity\Media;
use GFB\CommonBundle\Media\HttpFoundation\HandledFile;
use Sonata\MediaBundle\Entity\MediaManager;
use Sonata\MediaBundle\Provider\FileProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadMediaHelper
{
    /** @var MediaManager */
    private $mediaManager;

    /** @var ContainerInterface */
    private $container;

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
     * @return Media|null
     */
    public function upload($file, $context, $provider)
    {
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return false;
        }

        $media = new Media();
        $media->setBinaryContent($file);
        $media->setContext($context);
        $media->setProviderName($provider);

        /*$imageMimeTypes = array('image/jpeg', 'image/png', 'image/gif');
        $fileMimeTypes = array(
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword',
            'application/pdf',
            'application/x-pdf'
        );
        if (in_array($file->getMimeType(), $fileMimeTypes)) {
            $media->setProviderName('sonata.media.provider.file');
        }
        if (in_array($file->getMimeType(), $imageMimeTypes)) {
            $media->setProviderName($provider);
        }*/

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
                new HandledFile($tempFile)
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

        /** @var FileProvider $provider */
        $provider = $this->container->get($media->getProviderName());
        $format = $provider->getFormatName($media, $format);

        return $provider->generatePublicUrl($media, $format);
    }
}