<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Token;
use App\Providers\Booking\BookingInterface;
use App\Providers\Payment\PaymentInterface;
use App\Repository\ClientRepository;
use App\Repository\TokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $token = $payment->create($debt * 100);
        return $this->json([
            'token' => $token,
        ]);
    }

    private function getToken(
        Request $request,
        BookingInterface $booking,
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
