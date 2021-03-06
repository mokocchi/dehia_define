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
        if (($actividad->getEstado()->getNombre() == "Privado") || !$actividad->getDefinitiva()) {
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
        if ($actividad->getCerrada() || !$actividad->getDefinitiva()) {
            throw new ApiProblemException(
                new ApiProblem(
                    Response::HTTP_FORBIDDEN,
                    "La actividad está cerrada",
                    "La actividad está cerrada"
                )
            );
        }
        //for any actividad
        /** @var ActividadTareaRepository $actividadTareaRepository */
        $actividadTareaRepository = $this->getDoctrine()->getManager()->getRepository(ActividadTarea::class);
        $tasks = $actividadTareaRepository->findTareasByCodigo($codigo);
        return $this->handleView($this->view(["author" => $actividad->getAutor()->getGoogleid(), "results" => $tasks]));
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
        $iniciales = [];
        if ($actividad->getTipoPlanificacion()->getNombre() === "Secuencial") {
            $iniciales = [$actividadTareas[0]->getTarea()->getId()];
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
                "jumps" => count($jumps) == 0 ?
                    [] //no hay saltos en general
                    : (count($jumps[$tarea->getId()]) == 0 ? //esta tarea no tiene saltos
                        (($actividad->getTipoPlanificacion()->getNombre() === "Bifurcada") ?
                            [["on" => "ALL", "to" => "END", "answer" => null]]
                            : [])
                        : $jumps[$tarea->getId()])
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


        # https://stackoverflow.com/a/42058764
        function filter_filename($filename, $beautify=true) {
            // sanitize filename
            $filename = preg_replace(
                '~
                [<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
                [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
                [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
                [#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
                [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
                ~x',
                '-', $filename);
            // avoids ".", ".." or ".hiddenFiles"
            $filename = ltrim($filename, '.-');
            // maximize filename length to 255 bytes http://serverfault.com/a/9548/44086
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
            return $filename;
        }

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
                    filter_filename($actividad->getNombre() . '.json')
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }
}
