<?php

declare(strict_types=1);

namespace App\Service;

use App\Providers\Booking\Beds24\Entity\InvoiceItem;
use App\Providers\Booking\BookingInterface;
use Psr\Log\LoggerInterface;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class PaymentService
{
    public function __construct(
        private readonly BookingInterface $booking,
        private readonly LoggerInterface $logger,
        private readonly ClientService $clientService,
    ) {
    }

    public function handleSuccessfulPayment(string $payload, string $signature, string $endpointSecret): void
    {
        $event = Webhook::constructEvent($payload, $signature, $endpointSecret);
        $paymentIntent = $event->data->object;
        $bookingId = (int) $paymentIntent->metadata?->bookingId;
        $clientName = $paymentIntent->metadata?->client;
        $referer = $paymentIntent->metadata?->referer;
        $amount = $paymentIntent->amount;
        if (!$bookingId) {
            $this->logger->error('PaymentIntent does not contain bookingId', ['paymentIntent' => print_r($paymentIntent, true)]);
            throw new BadRequestException('PaymentIntent does not contain bookingId');
        }
        if (!$clientName) {
            $this->logger->error('PaymentIntent does not contain client name', ['paymentIntent' => print_r($paymentIntent, true)]);
            throw new BadRequestException('PaymentIntent does not client name');
        }
        $invoiceDescription = "Stripe payment for Booking № $bookingId (referer: $referer)";
        $this->booking->setToken($this->clientService->getTokenByName($clientName)->getToken());
        $this->booking->addInvoice($bookingId, InvoiceItem::PAYMENT, $amount / 100, $invoiceDescription);
        $this->booking->setPaidStatus($bookingId, 'paid');
    }
}
