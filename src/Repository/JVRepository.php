<?php

namespace App\Repository;

use App\Entity\JV;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JV>
 */
class JVRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JV::class);
    }

    public function findAllWithAverageNote(?int $limit = null, ?int $genreId = null): array
{
    $qb = $this->createQueryBuilder('jv')
        ->select('jv', 'AVG(n.valeur) as moyenneNote')
        ->leftJoin('jv.notes', 'n')
        ->groupBy('jv.id')
        ->orderBy('jv.dateSortie', 'DESC');

    if ($genreId) {
        $qb->andWhere('jv.genre = :genreId')
           ->setParameter('genreId', $genreId);
    }

    if ($limit) {
        $qb->setMaxResults($limit);
    }

    return $qb->getQuery()->getResult();
}

    

    //    /**
    //     * @return JV[] Returns an array of JV objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('j.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }
}