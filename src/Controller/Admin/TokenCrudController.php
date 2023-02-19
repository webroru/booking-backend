<?php

namespace App\Controller\Admin;

use App\Entity\Token;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;

class TokenCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Token::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextEditorField::new('token'),
            TextEditorField::new('refreshToken'),
            AssociationField::new('client'),
            DateTimeField::new('expiresAt')
                ->setFormat('Y-MM-dd HH:mm:ss'),
        ];
    }
}
