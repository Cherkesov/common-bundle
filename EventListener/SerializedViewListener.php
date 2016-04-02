<?php
/**
 * Created by PhpStorm.
 * User: GoForBroke
 * Date: 26.03.2016
 * Time: 23:34
 */

namespace GFB\CommonBundle\EventListener;


use Doctrine\Common\Annotations\CachedReader;
use GFB\CommonBundle\Exception\InvalidFormException;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SerializedViewListener
{
    const ANN_CLASS = 'AppBundle\Annotation\SerializedView';

    /**
     * @var CachedReader
     */
    private $reader;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$this->validRequest($event->getRequest())) {
            return;
        }

        $ex = $event->getException();

        if ($ex instanceof InvalidFormException) {
            $arr = [
                'errors' => [],
            ];
            $errors = $this->validator->validate($ex->getForm()->getData());
            foreach ($errors as $error) {
                $arr['errors'][$error->getPropertyPath()] = $error->getMessage();
            }
            $response = new JsonResponse($arr, $ex->getCode());
            $event->setResponse($response);
        }

        if ($ex instanceof \RuntimeException) {
            $response = new JsonResponse(
                [
                    'errors' => [$ex->getMessage()],
                ],
                $ex->getCode()
            );
            $event->setResponse($response);
        }
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        if (!$this->validRequest($request)) {
            return;
        }

        $val = $event->getControllerResult();
        if (!is_array($val) && !$val instanceof \Serializable) {
            return;
        }

        $format = $request->getRequestFormat(null);
        if (!$format) {
            $format = $request->get('_format', 'json');
        }
        $val = $this->serializer->serialize($val, $format);

        $response = new JsonResponse($val);
        $event->setResponse($response);
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function validRequest($request)
    {
        $controller = $request->attributes->get('_controller');
        $controller = explode('::', $controller);

        if (count($controller) < 2) {
            return false;
        }

        list($controllerClass, $methodName) = $controller;

        $controllerReflectionObject = new \ReflectionObject(new $controllerClass());
        $reflectionMethod = $controllerReflectionObject->getMethod($methodName);
        $methodAnnotation = $this->reader->getMethodAnnotation($reflectionMethod, self::ANN_CLASS);

        return !is_null($methodAnnotation);
    }

    /**
     * @param CachedReader $reader
     * @return $this
     */
    public function setReader($reader)
    {
        $this->reader = $reader;

        return $this;
    }

    /**
     * @param ValidatorInterface $validator
     * @return $this
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * @param Serializer $serializer
     * @return $this
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }
}