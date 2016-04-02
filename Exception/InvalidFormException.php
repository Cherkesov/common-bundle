<?php
/**
 * Created by PhpStorm.
 * User: GoForBroke
 * Date: 28.03.2016
 * Time: 22:57
 */

namespace GFB\CommonBundle\Exception;


use Symfony\Component\Form\Form;

class InvalidFormException extends \RuntimeException
{
    /** @var Form */
    protected $form;

    /**
     * InvalidFormException constructor.
     * @param Form $form
     * @param string $message
     * @param int $code
     * @param null|\Exception $previous
     */
    public function __construct(Form $form, $message = '', $code = 400, $previous = null)
    {
        $this->form = $form;
        parent::__construct('', $code, $previous);
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }
}