<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminCrudController extends AbstractCrudController
{
    private bool $isAdmin;

    public function __construct(
        private readonly Security $security,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        $this->isAdmin = in_array('ROLE_ADMIN', $this->security->getUser()->getRoles(), true);
    }

    public static function getEntityFqcn(): string
    {
        return Admin::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $action) =>
                $action->displayIf(fn($entity) => $this->isAdmin || $this->getUser() === $entity))
            ->update(Crud::PAGE_INDEX, Action::NEW, fn(Action $action) =>
                $action->displayIf(fn() => $this->isAdmin))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn(Action $action) =>
                $action->displayIf(fn() => $this->isAdmin));
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

    public function edit(AdminContext $context)
    {
        if (!$this->isAdmin) {
            $subject = $context->getEntity()->getInstance();
            $this->denyAccessUnlessGranted('EDIT', $subject);
        }

        return parent::edit($context);
    }

    public function detail(AdminContext $context)
    {
        if (!$this->isAdmin) {
            $subject = $context->getEntity()->getInstance();
            $this->denyAccessUnlessGranted('VIEW', $subject);
        }

        return parent::detail($context);
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
