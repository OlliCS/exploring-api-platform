<?php

namespace App\Controller\Admin;

use App\Entity\Room;
use App\Entity\User;
use App\Entity\Booking;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    #[Route('/bookingsystem', name: 'bookingsystem')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('BookingSystem');
    }

    public function configureMenuItems(): iterable
    {
        // yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Meeting Rooms');
        yield MenuItem::linkToCrud('Rooms', 'fas fa-list', Room::class);
        yield MenuItem::section('Bookings');
        yield MenuItem::linkToRoute('Create New', 'fas fa-calendar', 'app_calendar');
        yield MenuItem::linkToCrud('Bookings', 'fas fa-handshake', Booking::class);

        yield MenuItem::section('Users');
        yield MenuItem::linkToCrud('User', 'fas fa-user', User::class);




    }
}
