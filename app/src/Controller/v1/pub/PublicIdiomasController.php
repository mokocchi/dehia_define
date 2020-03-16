<?php

namespace App\Controller\v1\pub;

use App\Controller\BaseController;
use App\Entity\Idioma;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/idiomas")
 */
class PublicIdiomasController extends BaseController
{
    /**
     * Lista todos los idiomas
     * @Rest\Get(name="get_idiomas")
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
     * @SWG\Tag(name="Idioma")
     * @return Response
     */
    public function getIdiomasAction(Request $request = null, EntityManager $em = null)
    {
        if(is_null($em)){
            $em = $this->getDoctrine()->getManager();
        }
        $repository = $em->getRepository(Idioma::class);
        $idiomas = $repository->findall();
        return $this->getViewHandler()->handle($this->getViewWithGroups(["results" => $idiomas], "select"), $request);
    }
}
