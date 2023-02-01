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
        $bookingId = $request->get('bookingId');
        if (!$bookingId) {
            throw new BadRequestException();
        }
        $token = $this->clientService->getTokenByOrigin($request->headers->get('origin', 'http://localhost'));
        $this->booking->setToken($token->getToken());
        $bookingDto = $this->booking->findById($bookingId);
        $debt = $bookingDto->debt;
        $paymentToken = $this->payment->create($debt * 100, $bookingId, $token->getClient()->getName());

        return $this->json([
            'token' => $paymentToken,
        ]);
    }
}
