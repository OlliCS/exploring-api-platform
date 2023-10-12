<?php

namespace App\Repository;

use App\Entity\Room;
use App\Entity\Booking;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Booking>
 *
 * @method Booking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Booking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Booking[]    findAll()
 * @method Booking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function isRoomAvailable(Room $room, $startDate, $endDate): bool
    {
        $queryBuilder = $this->createQueryBuilder('b');

        $queryBuilder->select('COUNT(b.id)')
            ->where('b.room = :room')
            ->andWhere('b.startDate <= :endDate')
            ->andWhere('b.endDate >= :startDate')
            ->setParameter('room', $room)
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ;

        $result = $queryBuilder->getQuery()->getSingleScalarResult();

        return $result == 0;
    }








//    /**
//     * @return Booking[] Returns an array of Booking objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Booking
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
