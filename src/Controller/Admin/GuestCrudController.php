<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Guest;
use App\Enum\DocumentType;
use App\Enum\Gender;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GuestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Guest::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('bookingId')->setDisabled(),
            TextField::new('firstName'),
            TextField::new('lastName'),
            TextField::new('documentNumber'),
            ChoiceField::new('documentType')
                ->setChoices(array_combine(
                    array_map(fn(DocumentType $e) => $e->value, DocumentType::cases()),
                    DocumentType::cases()
                )),

            DateField::new('dateOfBirth')
                ->setFormat('yyyy-MM-dd')
                ->setTimezone('Europe/Prague'),

            TextField::new('nationality'),
            ChoiceField::new('gender')
                ->setChoices(array_combine(
                    array_map(fn(Gender $e) => $e->value, Gender::cases()),
                    Gender::cases()
                )),

            DateField::new('checkOutDate')
                ->setFormat('yyyy-MM-dd')
                ->setTimezone('Europe/Prague'),

            TimeField::new('checkOutTime')
                ->setFormat('HH:mm')
                ->setTimezone('Europe/Prague'),

            IntegerField::new('cityTaxExemption'),
            TextField::new('referer')->setDisabled(),
            TextField::new('room')->setDisabled(),
            BooleanField::new('isReported'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $sendToGov = Action::new('sendToGov', 'Отправить', 'fa fa-paper-plane')
            ->linkToCrudAction('sendToGov') // Метод ниже
            ->addCssClass('btn btn-warning');

        return $actions
            ->add(Crud::PAGE_INDEX, $sendToGov)
            ->add(Crud::PAGE_DETAIL, $sendToGov); // если хочешь и на странице деталей
    }

    public function sendToGov(AdminContext $context): RedirectResponse
    {
        $guest = $context->getEntity()->getInstance();

        // Здесь логика отправки, например:
        //$this->someService->sendGuestToGov($guest);

        $this->addFlash('success', 'The guest information has been sent to the government.');

        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(GuestCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }
}
