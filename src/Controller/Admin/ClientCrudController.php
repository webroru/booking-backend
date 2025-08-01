<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Client;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class ClientCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Client::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('domain'),
            TextField::new('name'),
            TimeField::new('checkInTime')
                ->setFormat('HH:mm')
                ->setTimezone('Europe/Prague'),
            BooleanField::new('isAutoSend'),
            AssociationField::new('admin'),
        ];
    }
}
