<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Token;
use App\Providers\Booking\BookingInterface;
use App\Providers\PhotoStorage\Local\Local;
use App\Repository\ClientRepository;
use App\Repository\PhotoRepository;
use App\Repository\TokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_bookg')]
class BookingController extends AbstractController
{
    #[Route('/booking', methods: ['GET'])]
    public function index(
        Request $request,
        BookingInterface $booking,
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

    #[Route('/booking/{id<\d+>}/photo', methods: ['POST'])]
    public function uploadPhoto(
        Request $request,
        int $id,
        Booking $booking,
        ClientRepository $clientRepository,
        TokenRepository $tokenRepository,
        Local $photoStorage,
    ): JsonResponse {
        $token = $this->getToken($request, $booking, $clientRepository, $tokenRepository);
        $booking->setToken($token->getToken());
        $bookingDto = $booking->findById($id);
        /** @var UploadedFile $file */
        $file = $request->files->get('photo');

        if (!$file) {
            throw new FileException("Field 'photo' is empty");
        }

        if (!$this->isImage($file)) {
            throw new FileException("The file is not an Image");
        }

        if (!$file->getExtension()) {
            $file = $file->move($file->getPath(), $file->getBasename() . '.' . $file->guessExtension());
        }

        $photo = $photoStorage->put($bookingDto, $file->getRealPath());
        $booking->addPhoto($id, $photo->getUrl());

        return $this->json([
            'data' => $photo->getId(),
        ]);
    }

    #[Route('/booking/{id<\d+>}/photo/{photoId<\d+>}', methods: ['DELETE'])]
    public function deletePhoto(
        Request $request,
        int $id,
        int $photoId,
        PhotoRepository $photoRepository,
        Booking $booking,
        ClientRepository $clientRepository,
        TokenRepository $tokenRepository,
        Local $photoStorage,
    ): JsonResponse {
        $photo = $photoRepository->find($photoId);
        if (!$photo) {
            throw new NotFoundHttpException("Photo $photoId is not found");
        }

        $token = $this->getToken($request, $booking, $clientRepository, $tokenRepository);
        $booking->setToken($token->getToken());

        $photoStorage->remove($photo);
        $booking->removePhoto($id, $photo->getUrl());

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

    private function isImage(UploadedFile $file): bool
    {
        return str_contains($file->getMimeType(), 'image');
    }
}
