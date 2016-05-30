<?php
namespace GFB\CommonBundle\SonataMediaBundle\Resizer;

use Gaufrette\File;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;

class RectangleResizer implements ResizerInterface
{
    protected $adapter;
    protected $metadata;
    protected $mode;

    /**
     * @param ImagineInterface $adapter
     * @param MetadataBuilderInterface $metadata
     */
    public function __construct(ImagineInterface $adapter, MetadataBuilderInterface $metadata)
    {
        $this->adapter = $adapter;
        $this->metadata = $metadata;

        $this->mode = ImageInterface::THUMBNAIL_OUTBOUND;
    }

    /**
     * @inheritdoc
     */
    public function resize(MediaInterface $media, File $in, File $out, $format, array $settings)
    {
        $image = $this->adapter->load($in->getContent());

        $ratio = $this->getRatio($media, $settings);

        $point = new Point(
            (int)($image->getSize()->getWidth() - $settings['width'] / $ratio) / 2,
            (int)($image->getSize()->getHeight() - $settings['height'] / $ratio) / 2
        );
        $box = $this->getBox($media, $settings);

        $content = $image
            ->crop($point, $box)
            ->thumbnail(
                new Box($settings['width'], $settings['height']),
                ImageInterface::THUMBNAIL_OUTBOUND
            )
            ->get($format, array('quality' => $settings['quality']));

        $out->setContent($content, $this->metadata->get($media, $out->getName()));
    }

    /**
     * @inheritdoc
     */
    public function getBox(MediaInterface $media, array $settings)
    {
        $ratio = $this->getRatio($media, $settings);

        return new Box(
            (int)($settings['width'] / $ratio),
            (int)($settings['height'] / $ratio)
        );
    }

    /**
     * @param MediaInterface $media
     * @param array $settings
     * @return float|mixed
     */
    public function getRatio(MediaInterface $media, array &$settings)
    {
        $size = $media->getBox();
        if ($settings['width'] && $settings['height']) {
            $ratios = array(
                $settings['width'] / $size->getWidth(),
                $settings['height'] / $size->getHeight(),
            );
            $ratio = max($ratios);
        } elseif ($settings['width']) {
            $ratio = $settings['width'] / $size->getWidth();
            $settings['height'] = (int)($size->getHeight() * $ratio);
        } elseif ($settings['height']) {
            $ratio = $settings['height'] / $size->getHeight();
            $settings['width'] = (int)($size->getWidth() * $ratio);
        } else {
            throw new \RuntimeException(
                sprintf(
                    'Width or Height parameter must be defined in context "%s" for provider "%s"',
                    $media->getContext(),
                    $media->getProviderName()
                )
            );
        }

        return $ratio;
    }
}