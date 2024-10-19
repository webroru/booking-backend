<?php

declare(strict_types=1);

namespace App\Controller;

use App\Providers\Booking\BookingInterface;
use App\Providers\Payment\PaymentInterface;
use App\Service\ClientService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_payment')]
class PaymentApiController extends AbstractApiController
{
    public function __construct(
        private readonly ClientService $clientService,
        private readonly BookingInterface $booking,
        private readonly PaymentInterface $payment,
        RequestStack $requestStack,
    ) {
        parent::__construct($clientService, $requestStack);
    }

    #[Route('/payment', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $bookings = $request->get('bookings');
        if (!$bookings) {
            throw new BadRequestException();
        }

        $debt = 0;
        $metadata = [
            'client' => $this->clientService->getClient()->getName(),
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
