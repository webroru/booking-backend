<?php

declare(strict_types=1);

namespace App\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IntegrationsController extends AbstractController
{
    #[Route(path: '/integrations', name: 'dashboard_integrations')]
    public function index(): Response
    {
        return $this->render('integrations.html.twig');
    }
}
