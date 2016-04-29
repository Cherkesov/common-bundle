<?php
/**
 * Created by PhpStorm.
 * User: GoForBroke
 * Date: 29.04.2016
 * Time: 19:35
 */

namespace GFB\CommonBundle\SonataMediaBundle\Resizer;


use Gaufrette\File;
use Imagine\Exception\InvalidArgumentException;
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

        $point = new Point(
            (int)($image->getSize()->getWidth() * $ratio - $settings['width']) / 2,
            (int)($image->getSize()->getHeight() * $ratio - $settings['height']) / 2
        );
        $box = new Box($settings['width'], $settings['height']);

        $content = $image
            ->resize(
                new Box(
                    $size->getWidth() * $ratio,
                    $size->getHeight() * $ratio
                )
            )
            ->crop($point, $box)
            ->thumbnail($box, ImageInterface::THUMBNAIL_OUTBOUND)
            ->get($format, array('quality' => $settings['quality']));

        $out->setContent($content, $this->metadata->get($media, $out->getName()));
    }

    /**
     * @inheritdoc
     */
    public function getBox(MediaInterface $media, array $settings)
    {
        // TODO: move code to me, please!
    }
}