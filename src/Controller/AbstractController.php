<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Token;
use App\Providers\Booking\BookingInterface;
use App\Repository\ClientRepository;
use App\Repository\TokenRepository;
use Symfony\Component\HttpFoundation\Request;

class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    protected function getToken(
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
