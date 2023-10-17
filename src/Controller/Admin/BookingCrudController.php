<?php

namespace App\Controller\Admin;

use DateTime;
use Exception;
use DateTimeZone;
use App\Entity\Booking;
use App\Service\RouteService;
use App\Service\BookingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Routing\Generator\UrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class BookingCrudController extends AbstractCrudController
{

    private $bookingService;
    private $entityManager;
    private $urlGenerator;

    public function __construct(BookingService $bookingService, EntityManagerInterface $entityManager,RouteService $urlGenerator)
    {
        $this->bookingService = $bookingService;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
    }
    public static function getEntityFqcn(): string
    {
        return Booking::class;
    }
    public function createEntity(string $entityFqcn)
    {
        $timezone = new DateTimeZone('Europe/Amsterdam');
        $booking = new Booking(new DateTime(null, $timezone),new DateTime(null, $timezone),null,null);
        return $booking;

    }
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $bookingResponse = $this->bookingService->createBooking(
            $entityInstance->getRoom(),
            $entityInstance->getStartDate(),
            $entityInstance->getEndDate(),
            $entityInstance->getUser()

        );

        if (!$bookingResponse->isSuccess()) {
            $this->addFlash('error', $bookingResponse->getMessage());
        }
        else{
            $this->addFlash('success', $bookingResponse->getMessage());
        }
    }
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm()->hideOnIndex();
        yield DateTimeField::new('startDate')->setColumns(8)
        ->setFormat('dd/MM/yyy HH:mm ');
        yield DateTimeField::new('endDate')->setColumns(8)
        ->setFormat('HH:mm ');
        yield AssociationField::new('user')->setColumns(8)->setTemplatePath('admin/booking_user.html.twig')->hideOnForm();


        yield AssociationField::new('room')->setColumns(8);
        yield TextField::new('duration')->setColumns(8)->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions {
       $bookingToday = Action::new('bookingToday') 
            ->setLabel('Today Bookings')
            ->setIcon('fa fa-calendar')
            ->createAsGlobalAction()
            ->setCssClass('btn btn-primary')
            ->linkToRoute('today_bookings');
       
        return parent::configureActions($actions)
        ->setPermission(Action::DELETE,'ROLE_ADMIN')
        ->disable(Action::NEW)
        ->add(Crud::PAGE_INDEX, $bookingToday);
        
    }

    public function configureFilters(Filters $filters):Filters{
        return parent::configureFilters($filters)
        ->add('room')
        ->add('user')
        ->add('startDate')
        ->add('endDate');
        

    }

    #[Route('/today_bookings', name: 'today_bookings')]
    public function todayBookings(): Response{
        $startOfDay = new DateTime('today 00:00:00');
        $endOfDay = new DateTime('today 23:59:59');

        $filtersStart = $this->createFilter('startDate', '>=', $startOfDay->format('Y-m-d H:i:s'));
        $filtersEnd = $this->createFilter('startDate', '<=', $endOfDay->format('Y-m-d H:i:s'));

        $filters = array_merge($filtersStart, $filtersEnd);
        
        $url = $this->urlGenerator->generateUrl('booking', 'index', $filters);

        return $this->redirect($url);
    }

    private function createFilter(string $filter,string $comparison,$value) : array
    {
        return [$filter => ['comparison' => $comparison, 'value' => $value]];
    }


    
}
