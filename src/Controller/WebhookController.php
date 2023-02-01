<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/webhook', name: 'webhook')]
class WebhookController extends AbstractController
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    #[Route('/stripe', methods: ['POST'])]
    public function stripe(Request $request,): JsonResponse
    {
        $endpointSecret = $this->getParameter('endpoint_secret');
        $payload = $request->getContent();
        $signature = $request->server->get('HTTP_STRIPE_SIGNATURE');
        $this->paymentService->handleSuccessfulPayment($payload, $signature, $endpointSecret);

        return $this->json([]);
    }
}
