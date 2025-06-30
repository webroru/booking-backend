<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Info;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class InfoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Info::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('hotelName'),
            TextField::new('address'),
            TextEditorField::new('rules'),
            TextEditorField::new('checkoutInfo'),
            TextField::new('callTime'),
            TextField::new('phoneNumber'),
            TextEditorField::new('howToMakeIt'),
            TextEditorField::new('facilities'),
            TextEditorField::new('extras'),
            TextEditorField::new('instruction'),
            TextEditorField::new('cashPaymentInstruction'),
            TextEditorField::new('paymentDisagree'),
            TextEditorField::new('checkInTime'),
            TextEditorField::new('checkOutTime'),
            TextField::new('locale'),
            AssociationField::new('client'),
        ];
    }
}
