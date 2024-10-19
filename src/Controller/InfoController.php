<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Info;
use App\Exception\TokenNotFoundException;
use App\Providers\Booking\BookingInterface;
use App\Serializer\Normalizer;
use App\Service\ClientService;
use App\Service\NotificationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_info')]
class InfoController extends AbstractApiController
{
    public function __construct(
        private readonly Normalizer $normalizer, // We need it?
        private readonly ClientService $clientService,
        private readonly NotificationService $notificationService,
        private readonly BookingInterface $booking,
        RequestStack $requestStack,
    ) {
        parent::__construct($clientService, $requestStack);
    }

    #[Route('/info', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $locale = $request->getLocale();
        $defaultLocale = $request->getDefaultLocale();
        $client = $this->clientService->getClient();
        $info = $client->getInfo()->filter(fn (Info $info) => $info->getLocale() === $locale)->first();
        if (!$info) {
            $info = $client->getInfo()->filter(fn (Info $info) => $info->getLocale() === $defaultLocale)->first();
        }

        if (!$info) {
            throw new TokenNotFoundException("Information for {$client->getName()} not found");
        }

        return $this->json(['data' => $this->normalizer->normalize($info)]);
    }

    #[Route('/send-to-email', methods: ['POST'])]
    public function sendToEmail(Request $request): JsonResponse
    {
        $bookingIds = $request->get('bookingIds');
        $bookings = $this->booking->findBy(['id' => $bookingIds]);
        $email = $request->get('email');
        $this->notificationService->sendBookingDetails($bookings, $email);

        return $this->json(['data' => 'ok']);
    }
}
