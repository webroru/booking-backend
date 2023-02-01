<?php

declare(strict_types=1);

namespace App\Controller;

use App\Providers\Booking\Beds24\Entity\InvoiceItem;
use App\Providers\Booking\BookingInterface;
use App\Service\ClientService;
use Psr\Log\LoggerInterface;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/webhook', name: 'webhook')]
class WebhookController extends AbstractController
{
    public function __construct(
        private readonly BookingInterface $booking,
        private readonly LoggerInterface $logger,
        private readonly ClientService $clientService,
    ) {
    }

    #[Route('/stripe', methods: ['POST'])]
    public function stripe(Request $request,): JsonResponse
    {
        $endpointSecret = $this->getParameter('endpoint_secret');
        $payload = $request->getContent();
        $signature = $request->server->get('HTTP_STRIPE_SIGNATURE');
        $event = Webhook::constructEvent($payload, $signature, $endpointSecret);
        $paymentIntent = $event->data->object;
        $bookingId = (int) $paymentIntent->metadata?->bookingId;
        $clientName = $paymentIntent->metadata?->client;
        $amount = $paymentIntent->amount;
        if (!$bookingId) {
            $this->logger->error('PaymentIntent does not contain bookingId', ['paymentIntent' => print_r($paymentIntent, true)]);
            throw new BadRequestException('PaymentIntent does not contain bookingId');
        }
        if (!$clientName) {
            $this->logger->error('PaymentIntent does not contain client name', ['paymentIntent' => print_r($paymentIntent, true)]);
            throw new BadRequestException('PaymentIntent does not client name');
        }
        $this->booking->setToken($this->clientService->getTokenByName($clientName)->getToken());
        $this->booking->addInvoice($bookingId, InvoiceItem::PAYMENT, $amount / 100);
        $this->booking->setPaidStatus($bookingId, 'paid');
        return $this->json([]);
    }
}
