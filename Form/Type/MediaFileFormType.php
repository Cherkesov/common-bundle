<?php
/**
 * Created by PhpStorm.
 * User: GoForBroke
 * Date: 28.03.2016
 * Time: 23:29
 */

namespace GFB\CommonBundle\Form\Type;


use Doctrine\Common\Persistence\ObjectManager;
use GFB\CommonBundle\Form\DataTransformer\MediaFileDataTransformer;
use Sonata\MediaBundle\Entity\MediaManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaFileFormType extends AbstractType
{
    /** @var ObjectManager */
    protected $manager;

    /** @var MediaManager */
    protected $mediaManager;

    /**
     * MediaFileFormType constructor.
     * @param ObjectManager $manager
     * @param $mediaManager
     */
    public function __construct(ObjectManager $manager, MediaManager $mediaManager)
    {
        $this->manager = $manager;
        $this->mediaManager = $mediaManager;
    }

    private $defaults = array(
        'new_on_update' => false,
        'context' => 'default',
        'auto_upload' => true,
        'preview_format' => 'reference',
    );

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new MediaFileDataTransformer($this->manager);
        $builder->addViewTransformer($transformer);

        $builder
            ->add('id', 'hidden')
            ->add(
                'binaryContent',
                'file',
                array(
                    'required' => false,
                    'mapped' => false,
                    'data' => false,
                )
            )->add(
                'unlink',
                'checkbox',
                array(
                    'mapped' => false,
                    'data' => false,
                    'required' => false,
                )
            );

        $builder->addEventListener(
            FormEvents::BIND,
            function (FormEvent $event) {
                if ($event->getForm()->get('unlink')->getData()) {
                    $event->setData(null);
                }
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['context'] = $options['context'];
        $view->vars['auto_upload'] = $options['auto_upload'];
        $view->vars['preview_format'] = $options['preview_format'];
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults($this->defaults);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults($this->defaults);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'gfb_media_file_type';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return 'form';
    }
}