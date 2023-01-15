<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/webhook', name: 'webhook')]
class WebhookController extends AbstractController
{
    #[Route('/stripe', methods: ['POST'])]
    public function stripe(
        Request $request,
        LoggerInterface $logger,
    ): JsonResponse {
        $endpointSecret = $this->getParameter('endpoint_secret');
        $payload = $request->getContent();
        $signature = $request->server->get('HTTP_STRIPE_SIGNATURE');
        $event = Webhook::constructEvent($payload, $signature, $endpointSecret);
        $paymentIntent = $event->data->object;
        $logger->debug(print_r($paymentIntent, true));
        return $this->json([]);
    }
}
