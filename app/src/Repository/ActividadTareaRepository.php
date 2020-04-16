<?php

namespace App\Repository;

use App\Entity\ActividadTarea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ActividadTarea|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActividadTarea|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActividadTarea[]    findAll()
 * @method ActividadTarea[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActividadTareaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActividadTarea::class);
    }

    /**
     * @return ActividadTarea[] Returns an array of ActividadTarea objects
     */
    public function findByActividad($actividad)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.actividad = :actividad')
            ->setParameter('actividad', $actividad)
            ->orderBy('a.orden', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function hasTarea($actividad, $tarea)
    {
        return $this->createQueryBuilder('a')
            ->select("1")
            ->where("a.actividad = :actividad")
            ->andWhere("a.tarea = :tarea")
            ->setParameter("actividad", $actividad)
            ->setParameter("tarea", $tarea)
            ->getQuery()
            ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?ActividadTarea
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
