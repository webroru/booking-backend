<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;

class AbstractApiController extends AbstractController
{
    public function __construct(
        ClientService $clientService,
        RequestStack $requestStack,
    ) {
        $request = $requestStack->getCurrentRequest();
        if ($request) {
            $clientService->setClientByOrigin($request->headers->get('origin', 'http://localhost'));
        }
    }
}
