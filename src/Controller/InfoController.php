<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Booking;
use App\Entity\Info;
use App\Exception\TokenNotFoundException;
use App\Serializer\Normalizer;
use App\Service\ClientService;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_info')]
class InfoController extends AbstractController
{
    public function __construct(
        private readonly Normalizer $normalizer,
        private readonly ClientService $clientService,
        private readonly NotificationService $notificationService,
    ) {
    }

    #[Route('/info', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $locale = $request->getLocale();
        $defaultLocale = $request->getDefaultLocale();
        $client = $this->clientService->getClientByOrigin($request->headers->get('origin', 'http://localhost'));
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
        $bookingData = $request->get('booking');
        $bookingDto = new Booking(...$bookingData);
        $email = $request->get('email');
        $this->notificationService->sendBookingDetails($bookingDto, $email);

        return $this->json(['data' => 'ok']);
    }
}
