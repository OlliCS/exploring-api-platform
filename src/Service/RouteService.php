<?php 

namespace App\Service;

use App\Controller\Admin\BookingCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class RouteService
{
    private $adminUrlGenerator;
    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public function generateUrl(string $entity, string $method, array $filters = []): ?string {
        $controllerClass = $this->getControllerClass($entity);
        $action = $this->getActionName($method);
    
        if ($controllerClass !== null && $action !== null) {
            return $this->adminUrlGenerator
                ->setController($controllerClass)
                ->setAction($action)
                ->set('filters', $filters)
                ->generateUrl();
        }
        
        return null;
    }

    private function getControllerClass(string $entity): ?string {
        $controllerClasses = [
            'booking' => BookingCrudController::class,
            'room' => RoomCrudController::class,
            'user' => UserCrudController::class,

            
        ];
        return $controllerClasses[$entity] ?? null;
    }

    private function getActionName(string $method): ?string {
        $actionNames = [
            'index' => Action::INDEX,
            'edit' => Action::EDIT,
            'new' => Action::NEW,
            'detail' => Action::DETAIL,
        ];
    
        return $actionNames[$method] ?? null;
    }
}