<?php

declare(strict_types=1);

namespace App\Service;

use App\Providers\Booking\Beds24\Entity\InvoiceItem;
use App\Providers\Booking\BookingInterface;
use Psr\Log\LoggerInterface;
use Stripe\Event;
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
        if ($event->type !== Event::PAYMENT_INTENT_SUCCEEDED) {
            return;
        }
        $paymentIntent = $event->data->object;
        $clientName = $paymentIntent->metadata?->client;
        $bookingData = json_decode($paymentIntent->metadata?->bookingData ?? '[]', true);
        if (!$clientName) {
            return;
        }
        $this->booking->setToken($this->clientService->getTokenByName($clientName)->getToken());

        foreach ($bookingData as ['amount' => $amount, 'bookingId' => $bookingId, 'referer' => $referer]) {
            if (!$bookingId) {
                $message = 'PaymentIntent does not contain bookingId';
                $this->logger->error($message, ['paymentIntent' => print_r($paymentIntent, true)]);
                throw new BadRequestException($message);
            }
            $invoiceDescription = "Stripe payment for Booking № $bookingId (referer: $referer)";
            $this->booking->addInvoice($bookingId, BookingInterface::PAYMENT, $amount, $invoiceDescription);
            $this->booking->setPaidStatus($bookingId, 'paid');
        }
    }
}
