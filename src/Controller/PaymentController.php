<?php

declare(strict_types=1);

namespace App\Controller;

use App\Providers\Booking\BookingInterface;
use App\Providers\Payment\PaymentInterface;
use App\Repository\ClientRepository;
use App\Repository\TokenRepository;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_payment')]
class PaymentController extends AbstractController
{
    #[Route('/payment', methods: ['POST'])]
    public function create(
        Request $request,
        BookingInterface $booking,
        ClientRepository $clientRepository,
        TokenRepository $tokenRepository,
        PaymentInterface $payment,
    ): JsonResponse {
        $bookingId = $request->get('bookingId');
        if (!$bookingId) {
            throw new BadRequestException();
        }
        $token = $this->getToken($request, $booking, $clientRepository, $tokenRepository);
        $booking->setToken($token->getToken());
        $bookingDto = $booking->findById($bookingId);
        $debt = $bookingDto->debt;
        $token = $payment->create($debt * 100, $bookingId);

        return $this->json([
            'token' => $token,
        ]);
    }
}
