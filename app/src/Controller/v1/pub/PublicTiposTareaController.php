<?php

namespace App\Controller\v1\pub;

use App\Controller\BaseController;
use App\Entity\TipoTarea;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

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
    public function getTiposTareaAction(Request $request = null, EntityManager $em = null)
    {
        if(is_null($em)) {
            $em = $this->getDoctrine()->getManager();
        }
        $repository = $em->getRepository(TipoTarea::class);
        $tipostarea = $repository->findall();
        return $this->getViewHandler()->handle($this->getViewWithGroups(["results" => $tipostarea], "select"), $request);
    }
}
