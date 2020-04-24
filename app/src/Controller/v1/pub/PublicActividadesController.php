<?php

namespace App\Controller\v1\pub;

use App\Api\ApiProblem;
use App\Api\ApiProblemException;
use App\Controller\BaseController;
use App\Entity\Actividad;
use App\Entity\ActividadTarea;
use App\Pagination\PaginationFactory;
use App\Repository\ActividadTareaRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/actividades")
 */
class PublicActividadesController extends BaseController
{
    /**
     * Lista todas las actividades públicas
     * @Rest\Get(name="get_actividades_public")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Operación exitosa"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error en el servidor"
     * )
     * 
     * @SWG\Tag(name="Actividad")
     * @return Response
     */
    public function getActividadesAction(Request $request, PaginationFactory $paginationFactory)
    {
        $filter = $request->query->get('filter');
        /** @var ActividadRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Actividad::class);
        $qb = $repository->findAllPublicQueryBuilder($filter);
        $paginatedCollection = $paginationFactory->createCollection($qb, $request, 'get_actividades_public');
        return $this->handleView($this->getViewWithGroups($paginatedCollection, "publico"));
    }

    private function checkActividadFound($id)
    {
        return $this->checkEntityFound(Actividad::class, $id);
    }

    private function checkActividadFoundByCodigo($codigo)
    {
        return $this->checkEntityFound(Actividad::class, $codigo, "codigo");
    }

    private function checkAccessActividad($actividad)
    {
        if ($actividad->getEstado()->getNombre() == "Privado") {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_FORBIDDEN, "La actividad es privada", "No se puede acceder a la actividad")
            );
        }
    }

    /**
     * Muestra una actividad pública
     * @Rest\Get("/{id}", name="show_actividad_public")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Operación exitosa"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error en el servidor"
     * )
     * 
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id de la actividad",
     *     schema={}
     * )
     * 
     * @SWG\Tag(name="Actividad")
     * @return Response
     */
    public function showActividadAction($id)
    {
        $actividad = $this->checkActividadFound($id);
        $this->checkAccessActividad($actividad);
        return $this->handleView($this->getViewWithGroups($actividad, "publico"));
    }

    /**
     * Lista las tareas de una actividad
     * @Rest\Get("/{id}/tareas", name="get_actividad_tareas_public")
     *
     * @SWG\Response(
     *     response=401,
     *     description="No autorizado"
     * )
     * 
     * @SWG\Response(
     *     response=403,
     *     description="Permisos insuficientes"
     * )
     * 
     * @SWG\Parameter(
     *     required=true,
     *     name="Authorization",
     *     in="header",
     *     type="string",
     *     description="Bearer token",
     * )
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Operación exitosa"
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Error en el servidor"
     * )
     * 
     * @SWG\Parameter(
     *     required=true,
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id de la actividad",
     *     schema={}
     * )
     * 
     * @SWG\Tag(name="Actividad")
     * @return Response
     */
    public function getActividadTareasAction($id)
    {
        $actividad = $this->checkActividadFound($id);
        $this->checkAccessActividad($actividad);
        $em = $this->getDoctrine()->getManager();
        /** @var ActividadTareaRepository $repository */
        $repository = $em->getRepository(ActividadTarea::class);
        $actividadTareas = $repository->findByActividad($actividad);
        $tareas = [];
        foreach ($actividadTareas as $actividadTarea) {
            $tarea = $actividadTarea->getTarea();
            $tareas[] = $tarea->setOrden($actividadTarea->getOrden());
        }
        return $this->handleView($this->getViewWithGroups(["results" => $tareas], "publico"));
    }

    /**
     * Lista los códigos de las tareas dado un código de actividad
     * @Rest\Get("/{codigo}/columns", name="get_columns_actividad")
     * @SWG\Tag(name="Actividad")
     * @return Response
     */
    public function getTareasIdsAction($codigo)
    {
        $actividad = $this->checkActividadFoundByCodigo($codigo);
        //for any actividad
        /** @var ActividadTareaRepository $actividadTareaRepository */
        $actividadTareaRepository = $this->getDoctrine()->getManager()->getRepository(ActividadTarea::class);
        $codigos = $actividadTareaRepository->findTareasByCodigo($codigo);
        $ids = array_map(function ($obj) {
            return $obj["codigo"];
        }, $codigos);
        return $this->handleView($this->view(["results" => $ids]));
    }

    /**
     * Download the JSON definition for the Actividad.
     * @Rest\Get("/{id}/data")
     *
     * @return Response
     */
    public function downloadActividadAction($id)
    {
        $actividad = $this->checkActividadFound($id);
        $JSON = [];
        $JSON["language"] = $actividad->getIdioma()->getCode();
        $educationalActivity = [
            "name" => $actividad->getNombre(),
            "code" => $actividad->getCodigo(),
            "goal" => $actividad->getObjetivo(),
            "sequential" => ($actividad->getTipoPlanificacion()->getNombre() != "Libre")
        ];
        $JSON["educationalActivity"] = $educationalActivity;
        $planificacion = $actividad->getPlanificacion();
        $jumps = [];
        $em = $this->getDoctrine()->getManager();
        /** @var ActividadTareaRepository $repository */
        $repository = $em->getRepository(ActividadTarea::class);
        $actividadTareas = $repository->findByActividad($actividad);
        foreach ($actividadTareas as $actividadTarea) {
            $jumps[$actividadTarea->getTarea()->getId()] = [];
        }
        $saltos = $planificacion->getSaltos();
        foreach ($saltos as $salto) {
            $jump = [
                "on" => $salto->getCondicion(),
                "to" => count($salto->getDestinoCodes()) == 0 ?  ["END"] : $salto->getDestinoCodes(),
                "answer" => $salto->getRespuesta()
            ];
            //multiple jumps for each tarea
            $jumps[$salto->getOrigen()->getId()][] = $jump;
        }
        $iniciales =[];
        if ($actividad->getTipoPlanificacion()->getNombre() === "Secuencial") {
            $iniciales = [ $actividadTareas[0]->getTarea()->getId() ];
        } else {
            $iniciales = $planificacion->getIniciales()->map(function ($elem) {
                return $elem->getId();
            })->toArray();
        }
        $opcionales = $planificacion->getOpcionales()->map(function ($elem) {
            return $elem->getId();
        })->toArray();

        $tasks = [];
        foreach ($actividadTareas as $actividadTarea) {
            $tarea = $actividadTarea->getTarea();
            $task = [
                "code" => $tarea->getCodigo(),
                "name" => $tarea->getNombre(),
                "instruction" => $tarea->getConsigna(),
                "initial" => in_array($tarea->getId(), $iniciales),
                "optional" => in_array($tarea->getId(), $opcionales),
                "type" => $tarea->getTipo()->getCodigo(),
                "jumps" => count($jumps) == 0 ? [] : $jumps[$tarea->getId()]
            ];
            foreach ($tarea->getExtra() as $key => $value) {
                $task[$key] = $value;
            }
            $tasks[] = $task;
        }
        $JSON["tasks"] = $tasks;
        //return $this->handleView($this->view($JSON));


        $fileContent = json_encode($JSON, JSON_PRETTY_PRINT);
        $response = new Response($fileContent);

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            iconv("UTF-8", "ASCII//TRANSLIT", $actividad->getNombre()) . '.json'
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
}
