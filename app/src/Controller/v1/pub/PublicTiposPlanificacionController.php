<?php

namespace App\Controller\v1\pub;

use App\Controller\BaseController;
use App\Entity\TipoPlanificacion;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/tipos-planificacion")
 */
class PublicTiposPlanificacionController extends BaseController
{
    /**
     * Lista todos los tipos de planificación
     * @Rest\Get(name="get_tipos_planificacion")
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
     * @SWG\Tag(name="Tipo planificación")
     * @return Response
     */
    public function getTiposPlanificacionAction(Request $request = null, EntityManager $em = null)
    {
        if (is_null($em)) {
            $em = $this->getDoctrine()->getManager();
        }
        $repository = $em->getRepository(TipoPlanificacion::class);
        $tipoPlanificacion = $repository->findall();
        return $this->getViewHandler()->handle($this->getViewWithGroups(["results" => $tipoPlanificacion], "select"), $request);
    }
}
