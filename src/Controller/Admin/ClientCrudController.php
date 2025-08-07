<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Client;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use Symfony\Bundle\SecurityBundle\Security;

class ClientCrudController extends AbstractCrudController
{
    private bool $isAdmin;

    public function __construct(
        private readonly Security $security,
    ) {
        $this->isAdmin = in_array('ROLE_ADMIN', $this->security->getUser()->getRoles(), true);
    }

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

        return $qb->andWhere('entity.admin = :admin')
            ->setParameter('admin', $admin);
    }

    public function edit(AdminContext $context)
    {
        if (!$this->isAdmin) {
            $client = $context->getEntity()->getInstance();
            $this->denyAccessUnlessGranted('EDIT', $client);
        }

        return parent::edit($context);
    }

    public function detail(AdminContext $context)
    {
        if (!$this->isAdmin) {
            $client = $context->getEntity()->getInstance();
            $this->denyAccessUnlessGranted('VIEW', $client);
        }

        return parent::detail($context);
    }
}
