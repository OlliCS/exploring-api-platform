<?php

namespace App\Controller;

use App\Service\SearchService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    private SearchService $searchService;
    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    #[Route('/api/searches', name: 'room_searcher')]
    public function search(Request $request): Response
    {
        $requestData = json_decode($request->getContent(), true);
        $date = $requestData['date'];
        $people = $requestData['people'];

        return $this->json([
            'people' => $people,
            'date' => $date,
        ]);



    }
}
