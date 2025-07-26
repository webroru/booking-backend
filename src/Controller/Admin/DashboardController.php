<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Entity\Client;
use App\Entity\Guest;
use App\Entity\Info;
use App\Entity\Photo;
use App\Entity\Beds24Token;
use App\Repository\AdminRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(ClientCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Code');
    }


    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)->addMenuItems(
            [
                MenuItem::linkToRoute('Change password', 'fa fa-lock', 'app_change_pass'),
            ]
        );
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Admins', 'fas fa-user', Admin::class);
        yield MenuItem::linkToCrud('Clients', 'fas fa-person', Client::class);
        yield MenuItem::linkToCrud('Beds24 Tokens', 'fas fa-key', Beds24Token::class);
        yield MenuItem::linkToCrud('Info', 'fas fa-circle-info', Info::class);
        yield MenuItem::linkToCrud('Guests', 'fas fa-people-group', Guest::class);
        yield MenuItem::linkToCrud('Photos', 'fas fa-camera', Photo::class);
    }

    #[Route('/admin/change/password', name: 'app_change_pass')]
    public function changeUserPassword(
        Request $request,
        UserPasswordHasherInterface $passwordEncoder,
        CsrfTokenManagerInterface $csrfTokenManager,
        AdminRepository $adminRepository
    ): Response {
        $oldPwd = (string) $request->request->get('old_password');
        $newPwd = (string) $request->request->get('new_password');

        if (empty($oldPwd) || empty($newPwd)) {
            return $this->render('security/change_password.html.twig', ['error' => null]);
        }

        $token = new CsrfToken('authenticate', (string) $request->request->get('_csrf_token'));

        if (!$csrfTokenManager->isTokenValid($token)) {
            return $this->render(
                'security/change_password.html.twig',
                [
                    'error' => 'Wrong csrf',
                ]
            );
        }

        $newPwdConfirm = (string)$request->request->get('new_password_confirm');

        /** @var Admin $admin */
        $admin = $this->getUser();

        if (!$passwordEncoder->isPasswordValid($admin, $oldPwd)) {
            return $this->render(
                'security/change_password.html.twig',
                [
                    'error' => 'Wrong password',
                ]
            );
        }

        if ($newPwd !== $newPwdConfirm) {
            return $this->render(
                'security/change_password.html.twig',
                [
                    'error' => 'Passwords are not equal',
                ]
            );
        }

        $adminRepository->upgradePassword(
            $admin,
            $passwordEncoder->hashPassword($admin, $newPwdConfirm)
        );

        return $this->redirect($this->generateUrl('admin'));
    }
}
