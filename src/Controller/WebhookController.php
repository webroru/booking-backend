<?php

declare(strict_types=1);

namespace App\Controller;

use App\Providers\Booking\BookingInterface;
use App\Service\ClientService;
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
        private readonly BookingInterface $booking,
        private readonly ClientService $clientService,
    ) {
    }

    #[Route('/stripe', methods: ['POST'])]
    public function stripe(Request $request): JsonResponse
    {
        $endpointSecret = $this->getParameter('endpoint_secret');
        $payload = $request->getContent();
        $signature = $request->server->get('HTTP_STRIPE_SIGNATURE');
        $this->paymentService->handleSuccessfulPayment($payload, $signature, $endpointSecret);

        return $this->json([]);
    }
    #[Route('/beds24/invoice/payment', methods: ['POST'])]
    public function beds24Booking(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $data = json_decode($payload, true);
        $bookingId = (int) $data['bookingId'];
        $amount = (float) $data['amount'];
        $invoiceDescription = $data['invoiceDescription'];
        $client = $data['client'];
        $this->clientService->setClientByName($client);
        $this->booking->addInvoice($bookingId, BookingInterface::PAYMENT, $amount, $invoiceDescription);

        return $this->json([]);
    }
}
