<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdminCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly RequestStack $requestStack,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Admin::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $isAdmin = in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true);

        return $actions
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $action) =>
                $action->displayIf(fn($entity) => $isAdmin || $this->getUser() === $entity))
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action) =>
                $action->displayIf(fn() => $isAdmin))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn(Action $action) =>
                $action->displayIf(fn($entity) => $isAdmin));
    }

    public function configureCrud(Crud $crud): Crud
    {
        $user = $this->getUser();
        $isClient = in_array('ROLE_CLIENT', $user->getRoles(), true);

        if (
            $this->requestStack->getCurrentRequest()?->attributes->get('_route') === 'admin'
            && $this->requestStack->getCurrentRequest()?->query->get('crudAction') === 'edit'
        ) {
            $entityId = $this->requestStack->getCurrentRequest()->query->get('entityId');
            if ($isClient && (string) $user->getId() !== (string) $entityId) {
                throw new AccessDeniedException('You are not allowed to edit this admin.');
            }
        }

        return $crud->setEntityPermission($isClient ? 'ROLE_CLIENT' : 'ROLE_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {
        $user = $this->getUser();
        $isClient = in_array('ROLE_CLIENT', $user->getRoles(), true);

        yield TextField::new('username')->setDisabled($isClient);

        if ($pageName === Crud::PAGE_NEW) {
            yield TextField::new('password')
                ->setFormType(PasswordType::class)
                ->setRequired(true)
                ->onlyOnForms();
        } else {
            yield TextField::new('newPassword')
                ->setFormType(PasswordType::class)
                ->setRequired(false)
                ->onlyOnForms()
                ->setHelp('Keep empty to not change the password');
        }

        yield ChoiceField::new('roles')
            ->setPermission('ROLE_ADMIN')
            ->setChoices([
                'Admin' => 'ROLE_ADMIN',
                'Client' => 'ROLE_CLIENT',
            ])
            ->renderExpanded()
            ->allowMultipleChoices();
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Admin) {
            return;
        }

        $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword());
        $entityInstance->setPassword($hashedPassword);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Admin) {
            return;
        }

        if (!empty($entityInstance->getNewPassword())) {
            $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getNewPassword());
            $entityInstance->setPassword($hashedPassword);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
