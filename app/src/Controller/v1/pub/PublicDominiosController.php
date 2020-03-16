<?php

namespace App\Controller\v1\pub;

use App\Controller\BaseController;
use App\Entity\Dominio;
use App\Repository\DominioRepository;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/dominios")
 */
class PublicDominiosController extends BaseController
{

    /**
     * Lista todos los dominios
     * @Rest\Get(name="get_dominios")
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
     * SWG\Parameter(
     *     name="nombre",
     *     in="query",
     *     type="string",
     *     description="Id del dominio",
     *     schema={}
     * )
     * 
     * @SWG\Tag(name="Dominio")
     * @return Response
     */
    public function getDominiosAction(Request $request, EntityManager $em = null)
    {
        if (is_null($em)) {
            $em = $this->getDoctrine();
        }
        /** @var DominioRepository $repository */
        $repository = $em->getRepository(Dominio::class);
        $nombre = $request->query->get("nombre");
        if ($nombre) {
            $dominios = $repository->findNombreLike($nombre);
        } else {
            $dominios = $repository->findall();
        }
        return $this->getViewHandler()->handle($this->getViewWithGroups(["results" => $dominios], "select"), $request);
    }

    private function checkDominioFound($id)
    {
        return $this->checkEntityFound(Dominio::class, $id);
    }

    /**
     * Muestra un dominio
     * @Rest\Get("/{id}", name="show_dominio")
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
     * SWG\Parameter(
     *     required=true,
     *     name="id",
     *     in="path",
     *     type="string",
     *     description="Id del dominio",
     *     schema={}
     * )
     * 
     * @SWG\Tag(name="Dominio")
     * @return Response
     */
    public function showDominioAction($id)
    {
        $dominio = $this->checkDominioFound($id);
        return $this->handleView($this->getViewWithGroups($dominio, "publico"));
    }
}
