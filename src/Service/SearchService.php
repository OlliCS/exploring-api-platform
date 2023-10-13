<?php

namespace App\Service;
use DateTime;
use App\Entity\Booking;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class SearchService{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    public function searchBookingOfSpecificDay() : array
    {
        $date = new DateTime('2023-10-15');
        $startDate = $date->setTime(0, 0, 0); // Set the time to the beginning of the day
        $endDate = $date->setTime(23, 59, 59); // Set the time to the end of the day

        $repository = $this->entityManager->getRepository(Booking::class);
        $queryBuilder = $repository->createQueryBuilder('b')
            ->where('b.startDate >= :startDate')
            ->andWhere('b.startDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        $bookings = $queryBuilder->getQuery()->getResult();

        return $bookings;
    }
}