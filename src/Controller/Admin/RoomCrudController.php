<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Room;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class RoomCrudController extends AbstractCrudController
{
    private bool $isAdmin;

    public function __construct(
        private readonly Security $security,
    ) {
        $this->isAdmin = in_array('ROLE_ADMIN', $this->security->getUser()->getRoles(), true);
    }

    public static function getEntityFqcn(): string
    {
        return Room::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('unit'),
            IntegerField::new('externalId'),
            IntegerField::new('governmentPortalId'),
            AssociationField::new('client'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $synchronize = Action::new('synchronize')
            ->createAsGlobalAction()
            ->linkToCrudAction('synchronize')
            ->addCssClass('btn');

        return $actions
            ->add(Crud::PAGE_INDEX, $synchronize);
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

    public function synchronize(AdminContext $context): RedirectResponse
    {
        try {
            $this->addFlash('success', 'Rooms are synchronized successfully');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Error rooms synchronization: ' . $e->getMessage());
        }

        $url = $this->container->get(AdminUrlGenerator::class)
            ->setController(RoomCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }
}
