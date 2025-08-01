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
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GuestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Guest::class;
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            return [
                IntegerField::new('bookingId'),
                TextField::new('firstName'),
                TextField::new('lastName'),
                TextField::new('propertyName'),
                TextField::new('room'),
                DateField::new('checkInDate')
                    ->setFormat('yyyy-MM-dd')
                    ->setTimezone('Europe/Prague'),
                DateField::new('checkOutDate')
                    ->setFormat('yyyy-MM-dd')
                    ->setTimezone('Europe/Prague'),
                DateTimeField::new('registrationDate')
                    ->setFormat('yyyy-MM-dd')
                    ->setTimezone('Europe/Prague'),
                TextField::new('referer'),
            ];
        }

        return [
            IntegerField::new('bookingId'),
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

            DateTimeField::new('registrationDate')
                ->setFormat('yyyy-MM-dd')
                ->setTimezone('Europe/Prague'),

            DateField::new('checkInDate')
                ->setFormat('yyyy-MM-dd')
                ->setTimezone('Europe/Prague'),

            DateField::new('checkOutDate')
                ->setFormat('yyyy-MM-dd')
                ->setTimezone('Europe/Prague'),

            TimeField::new('checkOutTime')
                ->setFormat('HH:mm')
                ->setTimezone('Europe/Prague'),

            IntegerField::new('cityTaxExemption'),
            TextField::new('referer'),
            TextField::new('propertyName'),
            TextField::new('room'),
            BooleanField::new('isReported'),
            AssociationField::new('client'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $isEnabled = true; // или false

        $autoSend = Action::new('autoSend')
            ->createAsGlobalAction()
            ->setTemplatePath('admin/actions/auto_send_switch.html.twig')
            ->setHtmlAttributes(['data-is-enabled' => $isEnabled ? '1' : '0'])
            ->linkToUrl('#');

        $sendToGov = Action::new('sendToGov', 'Send', 'fa fa-paper-plane')
            ->linkToCrudAction('sendToGov')
            ->addCssClass('btn btn-warning');

        return $actions
            ->add(Crud::PAGE_INDEX, $autoSend)
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

    #[Route('/admin/guest-auto-send', name: 'admin_guest_auto_send', methods: ['POST'])]
    public function toggleFlag(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $enabled = $data['enabled'] ?? false;

        $token = $request->headers->get('X-CSRF-TOKEN');
        if (!$this->isCsrfTokenValid('toggle_flag', $token)) {
            return new JsonResponse(['message' => 'Неверный CSRF токен'], 400);
        }

        // Логика обновления сущности
        // $entity = ...;
        // $entity->setFlag($enabled);
        // $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'message' => $enabled ?
                'Automatically send to the Government is enabled' :
                'Automatically send to the Government is disabled'
        ]);
    }
}
