<?php

declare(strict_types=1);

namespace App\Controller;

use App\Providers\Booking\BookingInterface;
use App\Providers\Payment\PaymentInterface;
use App\Service\ClientService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_payment')]
class PaymentController extends AbstractController
{
    public function __construct(
        private readonly ClientService $clientService,
        private readonly BookingInterface $booking,
        private readonly PaymentInterface $payment,
    ) {
    }

    #[Route('/payment', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $bookings = $request->get('bookings');
        if (!$bookings) {
            throw new BadRequestException();
        }
        $token = $this->clientService->getTokenByOrigin($request->headers->get('origin', 'http://localhost'));
        $this->booking->setToken($token->getToken());

        $debt = 0;
        $metadata = [
            'client' => $token->getClient()->getName(),
        ];
        $bookingData = [];
        foreach ($bookings as $bookingId) {
            $bookingDto = $this->booking->findById($bookingId);
            $debt += $bookingDto->debt;
            $bookingData[] = [
                'bookingId' => $bookingId,
                'referer' => $bookingDto->originalReferer,
                'amount' => $bookingDto->debt,
            ];
        }
        $metadata['bookingData'] = json_encode($bookingData);
        $paymentToken = $this->payment->create($debt * 100, $metadata);

        return $this->json([
            'token' => $paymentToken,
        ]);
    }
}
