<?php

namespace App\Controller\v1\pub;

use App\Controller\BaseController;
use App\Entity\TipoTarea;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

/**
 * @Route("/tipos-tarea")
 */
class PublicTiposTareaController extends BaseController
{
    /**
     * Lista todos los tipos de tarea
     * @Rest\Get(name="get_tipos_tarea")
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
     * @SWG\Tag(name="Tipo Tarea")
     * @return Response
     */
    public function getTipoTareaAction()
    {
        $repository = $this->getDoctrine()->getRepository(TipoTarea::class);
        $tipostarea = $repository->findall();
        return $this->handleView($this->getViewWithGroups(["results" => $tipostarea], "select"));
    }
}
