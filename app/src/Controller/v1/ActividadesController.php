<?php

namespace App\Controller\v1;

use App\Api\ApiProblem;
use App\Api\ApiProblemException;
use App\Controller\BaseController;
use App\Entity\Actividad;
use App\Entity\ActividadTarea;
use App\Entity\Dominio;
use App\Entity\Estado;
use App\Entity\Idioma;
use App\Entity\Planificacion;
use App\Entity\Tarea;
use App\Entity\TipoPlanificacion;
use App\Form\ActividadType;
use App\Pagination\PaginationFactory;
use App\Repository\ActividadRepository;
use App\Security\Voter\ActividadVoter;
use App\Security\Voter\TareaVoter;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;

/**
 * @Route("/actividades")
 */
class ActividadesController extends BaseController
{
    const BIFURCADA_NAME = "Bifurcada";

    /**
     * Lista todas las actividades del sistema
     * @Rest\Get(name="get_actividades")
     * @IsGranted("ROLE_ADMIN")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Operación exitosa"
     * )
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
     * @SWG\Response(
     *     response=500,
     *     description="Error en el servidor"
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
     * @SWG\Tag(name="Actividad")
     * @return Response
     */
    public function getActividadesAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Actividad::class);
        $codigo = $request->query->get("codigo");
        if (is_null($codigo)) {
            $actividades = $repository->findAll();
        } else {
            $actividades = $repository->findBy(["codigo" => $codigo]);
        }
        return $this->handleView($this->getViewWithGroups(["results" => $actividades], "autor"));
    }

    /**
     * Lista las actividades del usuario actual
     * 
     * @Rest\Get("/user", name="get_actividades_user")
     * @IsGranted("ROLE_AUTOR")
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Operación exitosa"
     * )
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
     * @SWG\Response(
     *     response=500,
     *     description="Error en el servidor"
     * )
     * 
     * @SWG\Parameter(
     *     name="Authorization",
     *     required=true,
     *     in="header",
     *     type="string",
     *     description="Bearer token",
     * )
     * 
     * @SWG\Tag(name="Actividad")
     * @return Response
     */
    public function getActividadesForUserAction(Request $request, PaginationFactory $paginationFactory)
    {
        $filter = $request->query->get('filter');
        /** @var ActividadRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Actividad::class);
        $qb = $repository->findAllQueryBuilder($filter, $this->getUser());
        $paginatedCollection = $paginationFactory->createCollection($qb, $request, 'get_actividades_user');
        return $this->handleView($this->getViewWithGroups($paginatedCollection, "autor"));
    }

    private function checkCodigoNotUsed($codigo, $em = null)
    {
        $this->checkPropertyNotUsed(Actividad::class, "codigo", $codigo, "Ya existe una actividad con el mismo código", $em);
    }

    private function checkActividadFound($id, $em = null)
    {
        return $this->checkEntityFound(Actividad::class, $id, null, $em);
    }

    private function checkTareaFound($id)
    {
        return $this->checkEntityFound(Tarea::class, $id);
    }

    private function checkTareaFoundByCodigo($codigo)
    {
        return $this->checkEntityFound(Tarea::class, $codigo, "codigo");
    }

    /**
     * Muestra una actividad
     * @Rest\Get("/{id}", name="show_actividad")
     * @IsGranted("ROLE_AUTOR")
     *
     * @SWG\Response(
     *     response=401,
     *     description="No autorizado"
     * )
     * 
     * @SWG\Response(
     *     response=403,
     *     description="Permisos insuficientes o La actividad es privada"
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
     *     response=404,
     *     description="Actividad no encontrada"
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
    public function showActividadAction($id, Request $request = null, EntityManager $em = null)
    {
        $actividad = $this->checkActividadFound($id, $em);
        $this->denyAccessUnlessGranted(ActividadVoter::ACCESS, $actividad);
        return $this->getViewHandler()->handle($this->getViewWithGroups($actividad, "autor"), $request);
    }

    /**
     * Crea una actividad
     * @Rest\Post(name="post_actividad")
     * @IsGranted("ROLE_AUTOR")
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
     * @SWG\Response(
     *     response=201,
     *     description="La actividad fue creada"
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Hubo un problema con la petición"
     * )
     * 
     * @SWG\Response(
     *     response=500,
     *     description="Error en el servidor"
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
     * @SWG\Parameter(
     *     required=true,
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre de la actividad",
     *     schema={}
     * )
     * 
     * @SWG\Parameter(
     *     required=true,
     *     name="objetivo",
     *     in="body",
     *     type="string",
     *     description="Objetivo de la actividad",
     *     schema={}
     * )
     * 
     * @SWG\Parameter(
     *     required=true,
     *     name="codigo",
     *     in="body",
     *     type="string",
     *     description="Código que identifica a la actividad",
     *     schema={}
     * )
     * 
     * @SWG\Parameter(
     *     required=true,
     *     name="dominio",
     *     in="body",
     *     type="integer",
     *     description="Id del dominio de la actividad",
     *     schema={}
     * )
     * 
     * @SWG\Parameter(
     *     required=true,
     *     name="idioma",
     *     in="body",
     *     type="integer",
     *     description="Id del idioma de la actividad",
     *     schema={}
     * )
     * 
     * @SWG\Parameter(
     *     required=true,
     *     name="tipoPlanificacion",
     *     in="body",
     *     type="integer",
     *     description="Id del tipo de planificación de la actividad",
     *     schema={}
     * )
     * 
     * @SWG\Parameter(
     *     required=true,
     *     name="estado",
     *     in="body",
     *     type="integer",
     *     description="Id del estado de la actividad",
     *     schema={}
     * )
     * 
     * @SWG\Tag(name="Actividad")
     * 
     * @return Response
     */
    public function postActividadAction(Request $request, EntityManager $em = null)
    {
        $actividad = new Actividad();
        $form = $this->createForm(ActividadType::class, $actividad);
        $data = $this->getJsonData($request);
        $this->checkRequiredParameters(["nombre", "objetivo", "codigo", "dominio", "idioma", "tipoPlanificacion", "estado"], $data);
        $this->checkCodigoNotUsed($data["codigo"], $em);
        $form->submit($data);
        $this->checkFormValidity($form);

        if (is_null($em)) {
            $em = $this->getDoctrine()->getManager();
        }
        $planificacion = new Planificacion();
        $em->persist($planificacion);
        $actividad->setPlanificacion($planificacion);
        $actividad->setAutor($this->getUser());
        $em->persist($actividad);
        $em->flush();

        $url = $this->generateUrl('show_actividad', ['id' => $actividad->getId() ?: 0]);
        return $this->getViewHandler()->handle($this->setGroupToView($this->view($actividad, Response::HTTP_CREATED, ["Location" => $url]), "autor"), $request);
    }

    /**
     * Actualiza una actividad
     * @Rest\Patch("/{id}",name="patch_actividad")
     * @IsGranted("ROLE_AUTOR")
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
     * @SWG\Response(
     *     response=404,
     *     description="Actividad no encontrada"
     * )
     * 
     * @SWG\Response(
     *     response=200,
     *     description="Operación exitosa"
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Hubo un problema con la petición"
     * )
     * 
     * @SWG\Response(
     *     response=500,
     *     description="Error en el servidor"
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
     * @SWG\Parameter(
     *     name="nombre",
     *     in="body",
     *     type="string",
     *     description="Nombre de la actividad",
     *     schema={}
     * )
     * 
     * @SWG\Parameter(
     *     name="objetivo",
     *     in="body",
     *     type="string",
     *     description="Objetivo de la actividad",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="dominio",
     *     in="body",
     *     type="integer",
     *     description="Id del dominio de la actividad",
     *     schema={}
     * )
     * 
     * @SWG\Parameter(
     *     name="idioma",
     *     in="body",
     *     type="integer",
     *     description="Id del idioma de la actividad",
     *     schema={}
     * )
     * 
     * @SWG\Parameter(
     *     name="tipoPlanificacion",
     *     in="body",
     *     type="integer",
     *     description="Id del tipo de planificación de la actividad",
     *     schema={}
     * )
     * 
     * @SWG\Parameter(
     *     name="estado",
     *     in="body",
     *     type="integer",
     *     description="Id del estado de la actividad",
     *     schema={}
     * )
     * 
     * @SWG\Tag(name="Actividad")
     * 
     * @return Response
     */
    public function patchActividadAction(Request $request, $id, EntityManager $em = null)
    {
        $data = $this->getJsonData($request);
        /** @var Actividad $actividad */
        $actividad = $this->checkActividadFound($id, $em);
        $this->denyAccessUnlessGranted(ActividadVoter::OWN, $actividad);
        if ($actividad->getDefinitiva()) {
            throw new ApiProblemException(
                new ApiProblem(
                    Response::HTTP_BAD_REQUEST,
                    "No se puede modificar una actividad publicada",
                    "No se puede modificar la actividad"
                )
            );
        }

        if (array_key_exists("codigo", $data)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, "No se puede modificar el código de una actividad", "No se puede modificar el código de una actividad")
            );
        }
        if (array_key_exists("nombre", $data) && !is_null($data["nombre"])) {
            $actividad->setNombre($data["nombre"]);
        }
        if (array_key_exists("objetivo", $data) && !is_null($data["objetivo"])) {
            $actividad->setObjetivo($data["objetivo"]);
        }

        if (is_null($em)) {
            $em = $this->getDoctrine()->getManager();
        }

        if (array_key_exists("dominio", $data) && !is_null($data["dominio"])) {
            $dominio = $em->getRepository(Dominio::class)->find($data["dominio"]);
            $actividad->setDominio($dominio);
        }

        if (array_key_exists("idioma", $data) && !is_null($data["idioma"])) {
            $idioma = $em->getRepository(Idioma::class)->find($data["idioma"]);
            $actividad->setIdioma($idioma);
        }

        if (array_key_exists("tipoPlanificacion", $data) && !is_null($data["tipoPlanificacion"])) {
            /** @var TipoPlanificacion */
            $tipoPlanificacion = $em->getRepository(TipoPlanificacion::class)->find($data["tipoPlanificacion"]);
            $actividad->setTipoPlanificacion($tipoPlanificacion);
            if ($tipoPlanificacion->getNombre() != self::BIFURCADA_NAME) {
                $planificacion = $actividad->getPlanificacion();
                $saltos = $planificacion->getSaltos();
                foreach ($saltos as $salto) {
                    $planificacion->removeSalto($salto);
                }
            }
        }

        if (array_key_exists("estado", $data) && !is_null($data["estado"])) {
            $estado = $em->getRepository(Estado::class)->find($data["estado"]);
            $actividad->setEstado($estado);
        }
        $em->persist($actividad);
        $em->flush();
        return $this->getViewHandler()->handle($this->getViewWithGroups($actividad, "autor"), $request);
    }

    /**
     * Publica una actividad
     * @Rest\Post("/publicadas",name="publish_actividad")
     * @IsGranted("ROLE_AUTOR")
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
     * @SWG\Response(
     *     response=404,
     *     description="Actividad no encontrada"
     * )
     * 
     * @SWG\Response(
     *     response=200,
     *     description="La actividad fue publicada"
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Hubo un problema con la petición"
     * )
     * 
     * @SWG\Response(
     *     response=500,
     *     description="Error en el servidor"
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
     * @SWG\Tag(name="Actividad")
     * 
     * @return Response
     */
    public function publishActividad(Request $request)
    {
        $data = $this->getJsonData($request);
        $this->checkRequiredParameters(["actividad"], $data);
        $actividad = $this->checkActividadFound($data["actividad"]);
        $this->denyAccessUnlessGranted(ActividadVoter::OWN, $actividad);

        $em = $this->getDoctrine()->getManager();
        if ($actividad->getDefinitiva()) {
            throw new ApiProblemException(
                new ApiProblem(
                    Response::HTTP_BAD_REQUEST,
                    "La actividad ya fue publicada",
                    "La actividad ya fue publicada"
                )
            );
        }
        $actividad->setDefinitiva(true);
        $em->persist($actividad);
        $em->flush();

        return $this->handleView($this->view(["actividad" => $actividad->getId()]));
    }

    /**
     * Da de baja una actividad
     * @Rest\Post("/cerradas",name="close_actividad")
     * @IsGranted("ROLE_AUTOR")
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
     * @SWG\Response(
     *     response=404,
     *     description="Actividad no encontrada"
     * )
     * 
     * @SWG\Response(
     *     response=200,
     *     description="La actividad fue cerrada"
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Hubo un problema con la petición"
     * )
     * 
     * @SWG\Response(
     *     response=500,
     *     description="Error en el servidor"
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
     * @SWG\Tag(name="Actividad")
     * 
     * @return Response
     */
    public function closeActividad(Request $request)
    {
        $data = $this->getJsonData($request);
        $this->checkRequiredParameters(["actividad"], $data);
        $actividad = $this->checkActividadFound($data["actividad"]);
        $this->denyAccessUnlessGranted(ActividadVoter::OWN, $actividad);

        if ($actividad->getCerrada()) {
            throw new ApiProblemException(
                new ApiProblem(
                    Response::HTTP_BAD_REQUEST,
                    "La actividad ya está cerrada",
                    "La actividad ya está cerrada"
                )
            );
        }
        $em = $this->getDoctrine()->getManager();
        $actividad->setCerrada(true);
        $em->persist($actividad);
        $em->flush();

        return $this->handleView($this->view(["actividad" => $actividad->getId()]));
    }

    /**
     * Elimina una actividad
     * @Rest\Delete("/{id}",name="delete_actividad")
     * @IsGranted("ROLE_AUTOR")
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
     * @SWG\Response(
     *     response=404,
     *     description="Actividad no encontrada"
     * )
     * 
     * @SWG\Response(
     *     response=204,
     *     description="La actividad fue borrada"
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Hubo un problema con la petición"
     * )
     * 
     * @SWG\Response(
     *     response=500,
     *     description="Error en el servidor"
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
     * @SWG\Tag(name="Actividad")
     * 
     * @return Response
     */
    public function deleteActividadAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $actividadRepository = $em->getRepository(Actividad::class);
        $actividad = $actividadRepository->find($id);
        if (!is_null($actividad)) {
            $this->denyAccessUnlessGranted(ActividadVoter::OWN, $actividad);
            $em->remove($actividad);
            $em->flush();
        }
        return $this->handleView($this->view(null, Response::HTTP_NO_CONTENT));
    }

    /**
     * Asigna un conjunto de tareas a una actividad
     * @Rest\Put("/{id}/tareas", name="put_tareas_actividad")
     * @IsGranted("ROLE_AUTOR")
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
     * @SWG\Response(
     *     response=200,
     *     description="La operación fue exitosa"
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Hubo un problema con la petición"
     * )
     * 
     * @SWG\Response(
     *     response=500,
     *     description="Error en el servidor"
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
     * @SWG\Parameter(
     *     required=true,
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id de la actividad",
     *     schema={}
     * )
     * 
     * @SWG\Parameter(
     *     required=true,
     *     name="tareas",
     *     in="body",
     *     type="array",
     *     description="Ids de la tareas",
     *     @SWG\Schema(type="array",
     *        @SWG\Items(
     *              type="integer")
     *     )
     * )
     * 
     * @SWG\Tag(name="Actividad")
     * @return Response
     */
    public function setTareasToActividad(Request $request, $id)
    {
        $data = $this->getJsonData($request);
        $this->checkRequiredParameters(["tareas"], $data);
        $em = $this->getDoctrine()->getManager();

        $actividad = $this->checkActividadFound($id);
        $this->denyAccessUnlessGranted(ActividadVoter::OWN, $actividad);
        if ($actividad->getDefinitiva()) {
            throw new ApiProblemException(
                new ApiProblem(
                    Response::HTTP_BAD_REQUEST,
                    "No se puede modificar una actividad publicada",
                    "No se puede modificar la actividad"
                )
            );
        }

        $this->removeTareasFromActividad($actividad);


        $tareas = [];
        $this->checkIsArray($data["tareas"], "tareas");
        foreach ($data["tareas"] as $tareaId) {
            $tareaDb = $this->checkTareaFound($tareaId);
            $this->denyAccessUnlessGranted(TareaVoter::OWN, $tareaDb);
            $tareas[] = $tareaDb;
        }
        $orden = 1;
        foreach ($tareas as $tarea) {
            $actividadTarea = new ActividadTarea();
            $actividadTarea->setActividad($actividad);
            $actividadTarea->setTarea($tarea);
            $actividadTarea->setOrden($orden);
            $orden++;
            $em->persist($actividadTarea);
            $actividad->addActividadTarea($actividadTarea);
        }
        $em->persist($actividad);
        $em->flush();
        return $this->handleView($this->getViewWithGroups(['results' => $tareas], "autor"));
    }

    private function removeTareasFromActividad($actividad)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(ActividadTarea::class);
        $actividadTareas = $repository->findBy(["actividad" => $actividad]);

        foreach ($actividadTareas as $actividadTarea) {
            $actividad->removeActividadTarea($actividadTarea);
        }
        $planificacion = $actividad->getPlanificacion();
        $saltos = $planificacion->getSaltos();
        foreach ($saltos as $salto) {
            $em->remove($salto);
        }
        $em->persist($planificacion);
    }

    /**
     * Lista las tareas de una actividad
     * @Rest\Get("/{id}/tareas", name="get_actividad_tareas")
     * @IsGranted("ROLE_AUTOR")
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
        $this->denyAccessUnlessGranted(ActividadVoter::ACCESS, $actividad);
        $em = $this->getDoctrine()->getManager();
        /** @var ActividadTareaRepository $repository */
        $repository = $em->getRepository(ActividadTarea::class);
        $actividadTareas = $repository->findByActividad($actividad);
        $tareas = [];
        foreach ($actividadTareas as $actividadTarea) {
            $tarea = $actividadTarea->getTarea();
            $tareas[] = $tarea->setOrden($actividadTarea->getOrden());
        }
        return $this->handleView($this->getViewWithGroups(["results" => $tareas], "autor"));
    }
}
