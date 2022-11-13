<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Token;
use App\Providers\Booking\Booking;
use App\Repository\ClientRepository;
use App\Repository\TokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class BookingController extends AbstractController
{
    #[Route('/booking', name: 'app_booking', methods: ['GET'])]
    public function index(
        Request $request,
        Booking $booking,
        ClientRepository $clientRepository,
        TokenRepository $tokenRepository,
    ): JsonResponse {
        $filter = $request->query->all();
        $today = (new \DateTime('- 10 days'))->format('Y-m-d');
        $lastDay = (new \DateTime('+ 10 days'))->format('Y-m-d');
        $filter['arrivalFrom'] = $today;
        $filter['arrivalTo'] = $lastDay;
        $filter['includeInvoiceItems'] = true;
        $filter['includeInfoItems'] = true;

        $token = $this->getToken($request, $booking, $clientRepository, $tokenRepository);

        $booking->setToken($token->getToken());
        return $this->json([
            'data' => $booking->findBy($filter),
        ]);
    }

    #[Route('/acceptRule', methods: ['POST'])]
    public function acceptRule(
        Request $request,
        Booking $booking,
        ClientRepository $clientRepository,
        TokenRepository $tokenRepository,
    ): JsonResponse {
        $orderId = $request->get('orderId');
        $isRuleAccepted = $request->get('isRuleAccepted');
        $token = $this->getToken($request, $booking, $clientRepository, $tokenRepository);
        $booking->setToken($token->getToken());
        $booking->acceptRule($orderId, $isRuleAccepted);

        return $this->json([
            'data' => 'ok',
        ]);
    }

    private function getToken(
        Request $request,
        Booking $booking,
        ClientRepository $clientRepository,
        TokenRepository $tokenRepository,
    ): Token {
        $origin = $request->headers->get('origin', 'http://localhost');
        $domain = parse_url($origin, PHP_URL_HOST);

        $client = $clientRepository->findOneBy(['domain' => $domain]);
        if (!$client) {
            throw new \Exception("Request from $domain is not allowed");
        }
        $token = $client->getToken();

        if (!$token) {
            throw new \Exception("Token in not found for $domain");
        }

        if ($token->getExpiresAt() <= (new \DateTime())) {
            $tokenDto = $booking->refreshToken($token->getRefreshToken());
            $token
                ->setToken($tokenDto->token)
                ->setExpiresAt(new \DateTime("+ $tokenDto->expiresIn seconds"))
            ;
            $tokenRepository->save($token, true);
        }

        return $token;
    }
}
