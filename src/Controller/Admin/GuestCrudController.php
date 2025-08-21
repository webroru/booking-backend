<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\Client;
use App\Entity\Guest;
use App\Enum\DocumentType;
use App\Enum\Gender;
use App\Exception\GuestReportException;
use App\Repository\ClientRepository;
use App\Service\GuestReportService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GuestCrudController extends AbstractCrudController
{
    private bool $isAdmin;

    public function __construct(
        private readonly Security $security,
        private readonly GuestReportService $guestReportService,
        private readonly ClientRepository $clientRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->isAdmin = in_array('ROLE_ADMIN', $this->security->getUser()->getRoles(), true);
    }

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
                AssociationField::new('room'),
                AssociationField::new('client'),
                DateField::new('checkInDate')
                    ->setFormat('yyyy-MM-dd')
                    ->setTimezone('Europe/Prague'),
                DateField::new('checkOutDate')
                    ->setFormat('yyyy-MM-dd')
                    ->setTimezone('Europe/Prague'),
                DateTimeField::new('registrationDate')
                    ->setFormat('yyyy-MM-dd HH:mm')
                    ->setTimezone('Europe/Prague'),
                TextField::new('referer'),
                BooleanField::new('isReported'),
            ];
        }

        return [
            IntegerField::new('bookingId'),
            TextField::new('firstName'),
            TextField::new('lastName'),
            TextField::new('documentNumber'),
            ChoiceField::new('documentType')
                ->setChoices(array_combine(
                    array_map(fn(DocumentType $e) => $e->name, DocumentType::cases()),
                    DocumentType::cases()
                )),

            DateField::new('dateOfBirth')
                ->setFormat('yyyy-MM-dd')
                ->setTimezone('Europe/Prague'),

            TextField::new('nationality'),
            ChoiceField::new('gender')
                ->setChoices(array_combine(
                    array_map(fn(Gender $e) => $e->name, Gender::cases()),
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
            AssociationField::new('room'),
            BooleanField::new('isReported'),
            AssociationField::new('client'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $clients = $this->isAdmin ?
            $this->clientRepository->findAll()
            : $this->security->getUser()->getClients()->toArray();

        $isEnabled = array_reduce(
            $clients,
            fn(bool $carry, Client $client) => $carry && $client->isAutoSend(),
            true,
        );

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
            ->add(Crud::PAGE_DETAIL, $sendToGov);
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $admin = $this->security->getUser();

        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        if ($this->isAdmin) {
            return $qb;
        }

        return $qb->join('entity.client', 'c')
            ->andWhere('c.admin = :admin')
            ->setParameter('admin', $admin);
    }

    public function edit(AdminContext $context): KeyValueStore|Response
    {
        if (!$this->isAdmin) {
            $subject = $context->getEntity()->getInstance();
            $this->denyAccessUnlessGranted('EDIT', $subject);
        }

        return parent::edit($context);
    }

    public function detail(AdminContext $context): KeyValueStore|Response
    {
        if (!$this->isAdmin) {
            $subject = $context->getEntity()->getInstance();
            $this->denyAccessUnlessGranted('VIEW', $subject);
        }

        return parent::detail($context);
    }

    public function sendToGov(AdminContext $context): RedirectResponse
    {
        /** @var Guest $guest */
        $guest = $context->getEntity()->getInstance();
        $client = $guest->getClient();
        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(GuestCrudController::class)
            ->setAction('index')
            ->generateUrl();

        if ($guest->getRoom()->getGovernmentPortalId() === null) {
            $this->addFlash('danger', 'The room is not linked to the Government Portal. '
                . 'Please set the Government Portal ID for the room.');

            return $this->redirect($url);
        }

        try {
            $this->guestReportService->reportGuests(
                [$guest],
                $client->getAjPesUsername(),
                $client->getAjPesPassword(),
            );
            $guest->setIsReported(true);
            $this->entityManager->flush();
            $this->addFlash('success', 'The guest information has been sent to the government.');
        } catch (GuestReportException $e) {
            $this->addFlash('danger', 'Error sending guest information: ' . $e->getMessage());
        }

        return $this->redirect($url);
    }

    #[Route('/admin/guest-auto-send', name: 'admin_guest_auto_send', methods: ['POST'])]
    public function toggleFlag(Request $request): JsonResponse
    {
        $clients = $this->isAdmin ?
            $this->clientRepository->findAll()
            : $this->security->getUser()->getClients()->toArray();

        $data = json_decode($request->getContent(), true);
        $enabled = $data['enabled'] ?? false;

        $token = $request->headers->get('X-CSRF-TOKEN');
        if (!$this->isCsrfTokenValid('toggle_flag', $token)) {
            return new JsonResponse(['message' => 'Неверный CSRF токен'], 400);
        }

        foreach ($clients as $client) {
            $client->setIsAutoSend($enabled);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => $enabled ?
                'Automatically send to the Government is enabled' :
                'Automatically send to the Government is disabled'
        ]);
    }
}
