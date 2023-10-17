<?php

namespace App\Controller;

use DateTime;
use App\Entity\Room;
use App\Service\SearchService;
use App\Service\BookingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

class SearchController extends AbstractController
{
    private SearchService $searchService;
    private BookingService $bookingService;
    private EntityManagerInterface $entityManager;
    public function __construct(SearchService $searchService, BookingService $bookingService,EntityManagerInterface $entityManager)
    {
        $this->searchService = $searchService;
        $this->bookingService = $bookingService;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/searches', name: 'room_searcher')]
    public function search(Request $request): Response
    {
        $requestData = json_decode($request->getContent(), true);
        $date = $requestData['date'];
        $people = $requestData['people'];

        $bookings = $this->searchService->findAvailableTimeSlotsForDateAndCapacity($date, $people);

        return $this->json($bookings);
    }

    #[Route("/api/bookings", name: "booking_create", methods: ["POST"])]
    public function create(Request $request,UserInterface $userInterface ): Response
    {
        $requestData = json_decode($request->getContent(), true);
        $start = $requestData['startDate'];
        $end = $requestData['endDate'];
        $roomId = $requestData['room'];

        $room = $this->entityManager->getRepository(Room::class)->find($roomId);
        
        $startDate = new DateTime($start);
        $endDate = new DateTime($end);

        $user = $this->getUser();



        $bookingResponse = $this->bookingService->createBooking($room,$startDate, $endDate,$user);

        return $this->json($bookingResponse);
    }

    #[Route('/calendar', name: 'app_calendar')]
    public function test(): Response
    {
        return $this->render('calendar/index.html.twig');
    }
}
