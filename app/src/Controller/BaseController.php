<?php

namespace App\Controller;

use App\Api\ApiProblem;
use App\Api\ApiProblemException;
use Exception;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController extends AbstractFOSRestController
{
    protected $logger;
    protected $serializer;
    protected $collectClient;
    protected $resultsClient;

    public function __construct(LoggerInterface $logger, SerializerInterface $serializer, $collectClient = null, $resultsClient = null)
    {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->collectClient = $collectClient ?: new \GuzzleHttp\Client(
            [
                'base_uri' => $_ENV["COLLECT_BASE_URL"]
            ]
        );
        $this->resultsClient = $resultsClient ?: new \GuzzleHttp\Client(
            [
                'base_uri' => $_ENV["RESULTS_BASE_URL"]
            ]
        );
    }

    protected function getViewWithGroups($object, $group)
    {
        $view = $this->view($object);
        return $this->setGroupToView($view, $group);
    }

    protected function setGroupToView($view, $group)
    {
        $context = new Context();
        $context->addGroup($group);
        $view->setContext($context);
        return $view;
    }

    protected function debug($variable)
    {
        throw new ApiProblemException(
            new ApiProblem(
                Response::HTTP_I_AM_A_TEAPOT,
                $this->serializer->serialize($variable ?: "null", "json"),
                "error"
            )
        );
    }

    protected function getJsonData(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (is_null($data)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, "No hay campos en el json", "Hubo un problema con la petición")
            );
        }
        return $data;
    }

    protected function checkPropertyNotUsed($class, $property, $value, $message, $em = null)
    {
        if (is_null($em)) {
            $em = $this->getDoctrine();
        }
        $entityDb = $em->getRepository($class)->findOneBy([$property => $value]);
        if (!is_null($entityDb)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $message, $message)
            );
        }
    }

    protected function checkRequiredParameters(array $parameters, array $data)
    {
        foreach ($parameters as $parameter) {
            if (!array_key_exists($parameter, $data) || is_null($data[$parameter]) || $data[$parameter] == "") {
                throw new ApiProblemException(
                    new ApiProblem(Response::HTTP_BAD_REQUEST, "Uno o más de los campos requeridos falta o es nulo", "Faltan datos")
                );
            }
        }
    }

    protected function checkEntityFound($class, $id, $property = null, $em = null)
    {
        if (is_null($em)) {
            $em = $this->getDoctrine()->getManager();
        }
        if ($property) {
            $entity = $em->getRepository($class)->findOneBy([$property => $id]);
        } else {
            $entity = $em->getRepository($class)->find($id);
        }
        if (is_null($entity)) {
            $path = explode('\\', $class);
            $class = array_pop($path);
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_NOT_FOUND, sprintf("No se encontró: %s %s", $class, $id), sprintf("No se encontró: %s", $class))
            );
        }
        return $entity;
    }

    protected function checkFormValidity($form)
    {
        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->logger->alert("Datos inválidos: " . json_decode($form->getErrors()));
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, "Se recibieron datos inválidos", "Datos inválidos"),
            );
        }
    }

    protected function checkIsArray($property, $propertyName)
    {
        if (!is_array($property)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, sprintf("El campo %s tiene que ser un array", $propertyName), "Hubo un problema con la petición")
            );
        }
    }
}
