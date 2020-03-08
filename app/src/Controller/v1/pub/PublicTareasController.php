<?php

namespace App\Controller\v1\pub;

use App\Api\ApiProblem;
use App\Api\ApiProblemException;
use App\Controller\BaseController;
use App\Entity\Estado;
use App\Entity\Tarea;
use App\Pagination\PaginationFactory;
use Exception;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/tareas")
 */
class PublicTareasController extends BaseController
{
    /**
     * Lista todas las tareas
     * @Rest\Get(name="get_tareas_public")
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
     * @SWG\Tag(name="Tarea")
     * @return Response
     */
    public function getTareasAction(Request $request, PaginationFactory $paginationFactory)
    {
        $nombre = $request->query->get('nombre');
        /** @var TareaRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Tarea::class);
        $qb = $repository->findAllPublicQueryBuilder($nombre);
        $paginatedCollection = $paginationFactory->createCollection($qb, $request, 'get_tareas_public');
        return $this->handleView($this->getViewWithGroups($paginatedCollection, "publico"));
    }

    private function checkTareaFound($id)
    {
        return $this->checkEntityFound(Tarea::class, $id);
    }

    private function checkAccessTarea($tarea)
    {
        if ($tarea->getEstado()->getNombre() == "Privado") {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_FORBIDDEN, "La tarea es privada o no pertenece al usuario actual", "No se puede acceder a la tarea")
            );
        }
    }

    /**
     * Shows a Tarea.
     * @Rest\Get("/{id}", name="show_tarea_public")
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
     * @SWG\Tag(name="Tarea")
     * @return Response
     */
    public function showTareaAction($id)
    {
        $tarea = $this->checkTareaFound($id);
        $this->checkAccessTarea($tarea);
        return $this->handleView($this->getViewWithGroups($tarea, "publico"));
    }
}
