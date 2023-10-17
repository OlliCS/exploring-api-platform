<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('email');
        yield TextField::new('password')->onlyOnForms();
        yield ArrayField::new('roles')->onlyOnForms();
        yield AssociationField::new('bookings')->hideOnForm();

    }

    public function configureActions(Actions $actions): Actions {
        return parent::configureActions($actions)
        ->setPermission(Action::DELETE,'ROLE_ADMIN')
        ->setPermission(Action::EDIT,'ROLE_ADMIN')
        ->setPermission(Action::NEW,'ROLE_ADMIN');

    }


}
