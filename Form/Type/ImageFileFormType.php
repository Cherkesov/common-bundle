<?php
/**
 * Created by PhpStorm.
 * User: GoForBroke
 * Date: 09.04.2016
 * Time: 0:06
 */

namespace GFB\CommonBundle\Form\Type;


use GFB\CommonBundle\SonataMediaBundle\Resizer\RectangleResizer;
use Sonata\MediaBundle\Provider\ImageProvider;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImageFileFormType extends AbstractType
{
    /**
     * @var Pool
     */
    protected $mediaPool;

    /**
     * ImageFileFormType constructor.
     * @param Pool $mediaPool
     */
    public function __construct($mediaPool)
    {
        $this->mediaPool = $mediaPool;
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $context = $this->mediaPool->getContext($options['context']);
        if (null == $context) {
            throw new \Exception(
                sprintf('Media context "%s" not found!', $options['context'])
            );
        }

        $formatName = $options['context'] . '_' . $options['preview_format'];
        $formatData = $context['formats'][$formatName];

        /** @var ImageProvider $provider */
        $provider = $this->mediaPool->getProvider('sonata.media.provider.image');
        $view->vars['preview']['resizer'] =
            (null != $provider->getResizer()
                && $provider->getResizer() instanceof RectangleResizer);

        if (intval($formatData['height']) == 0) {
            $formatData['height'] = $formatData['width'];
        }

        $view->vars['preview']['width'] = $formatData['width'];
        $view->vars['preview']['height'] = $formatData['height'];
        $view->vars['preview']['quality'] = $formatData['quality'];
    }

    /**
     * @inheritDoc
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $parentOptions = $resolver->resolve();
        $resolver->setDefaults(
            array_merge(
                $parentOptions,
                [
                    'context' => null,
                    'provider' => 'sonata.media.provider.image',
                    'preview_format' => 'reference',
                ]
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'gfb_image_file_type';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return 'sonata_media_type';
    }
}