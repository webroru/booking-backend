<?php

declare(strict_types=1);

namespace App\Controller;

use App\Providers\Booking\Beds24\Entity\InvoiceItem;
use App\Providers\Booking\BookingInterface;
use App\Repository\ClientRepository;
use App\Repository\TokenRepository;
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
    #[Route('/stripe', methods: ['POST'])]
    public function stripe(
        Request $request,
        BookingInterface $booking,
        ClientRepository $clientRepository,
        LoggerInterface $logger,
    ): JsonResponse {
        $endpointSecret = $this->getParameter('endpoint_secret');
        $payload = $request->getContent();
        $signature = $request->server->get('HTTP_STRIPE_SIGNATURE');
        $event = Webhook::constructEvent($payload, $signature, $endpointSecret);
        $paymentIntent = $event->data->object;
        $bookingId = (int) $paymentIntent->metadata?->bookingId;
        $clientName = $paymentIntent->metadata?->client;
        $amount = $paymentIntent->amount;
        if (!$bookingId) {
            $logger->error('PaymentIntent does not contain bookingId', ['paymentIntent' => print_r($paymentIntent, true)]);
            throw new BadRequestException('PaymentIntent does not contain bookingId');
        }
        if (!$clientName) {
            $logger->error('PaymentIntent does not contain client name', ['paymentIntent' => print_r($paymentIntent, true)]);
            throw new BadRequestException('PaymentIntent does not client name');
        }
        $client = $clientRepository->findOneByName($clientName);
        $booking->setToken($client->getToken()->getToken());
        $booking->addInvoice($bookingId, InvoiceItem::PAYMENT, $amount / 100);
        $booking->setPaidStatus($bookingId, 'paid');
        return $this->json([]);
    }
}
