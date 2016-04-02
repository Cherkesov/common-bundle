<?php
/**
 * Created by PhpStorm.
 * User: GoForBroke
 * Date: 02.04.2016
 * Time: 3:07
 */

namespace GFB\CommonBundle\Form\DataTransformer;


use Doctrine\Common\Persistence\ObjectManager;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class MediaFileDataTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @inheritdoc
     */
    public function transform($media)
    {
//        print_r($media); die;

        if (null == $media) {
            return array();
        }

        if (!$media instanceof MediaInterface) {
            return $media;
        }

        return array(
            'id' => $media->getId(),
            'media' => $media,
        );
    }

    /**
     * @inheritdoc
     */
    public function reverseTransform($arr)
    {
//        print_r($arr); die;

        if (!isset($arr['id'])) {
            return null;
        }

        $media = $this->manager
            ->getRepository('ApplicationSonataMediaBundle:Media')
            ->find($arr['id']);

        if (null === $media) {
            throw new TransformationFailedException(
                sprintf(
                    'An media with number "%s" does not exist!',
                    $arr['id']
                )
            );
        }

        return $media;
    }
}