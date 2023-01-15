<?php

declare(strict_types=1);

namespace App\Controller;

use App\Providers\Booking\BookingInterface;
use Psr\Log\LoggerInterface;
use Stripe\Webhook;
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
        LoggerInterface $logger,
    ): JsonResponse {
        $endpointSecret = $this->getParameter('endpoint_secret');
        $payload = $request->getContent();
        $signature = $request->server->get('HTTP_STRIPE_SIGNATURE');
        $event = Webhook::constructEvent($payload, $signature, $endpointSecret);
        $paymentIntent = $event->data->object;
        $bookingId = (int) $event->data->object->metadata?->bookingId;
        $amount = $paymentIntent->amount;
        if (!$bookingId) {
            $logger->error('PaymentIntent does not contain bookingId', ['paymentIntent' => print_r($paymentIntent, true)]);
            throw new BadRequestException('PaymentIntent does not contain bookingId');
        }
        $booking->addInvoice($bookingId, 'payment', $amount / 100);
        return $this->json([]);
    }
}
