<?php

namespace App\Repository;

use App\Entity\RoomSearcher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RoomSearcher>
 *
 * @method RoomSearcher|null find($id, $lockMode = null, $lockVersion = null)
 * @method RoomSearcher|null findOneBy(array $criteria, array $orderBy = null)
 * @method RoomSearcher[]    findAll()
 * @method RoomSearcher[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoomSearcherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomSearcher::class);
    }

//    /**
//     * @return RoomSearcher[] Returns an array of RoomSearcher objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RoomSearcher
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
