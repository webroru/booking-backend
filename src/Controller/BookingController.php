<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Booking;
use App\Providers\Booking\BookingInterface;
use App\Providers\PhotoStorage\Local\Local;
use App\Repository\PhotoRepository;
use App\Service\ClientService;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_booking')]
class BookingController extends AbstractController
{
    public function __construct(
        private readonly BookingInterface $booking,
        private readonly ClientService $clientService,
        private readonly PhotoRepository $photoRepository,
        private readonly Local $photoStorage,
    ) {
    }

    #[Route('/booking', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $filter = $request->query->all();
        $departureFrom = (new \DateTime())->format('Y-m-d');
        $arrivalTo = (new \DateTime('+3 days'))->format('Y-m-d');
        $filter['departureFrom'] = $departureFrom;
        $filter['arrivalTo'] = $arrivalTo;
        $filter['includeInvoiceItems'] = true;
        $filter['includeInfoItems'] = true;

        $token = $this->clientService->getTokenByOrigin($request->headers->get('origin', 'http://localhost'));
        $this->booking->setToken($token->getToken());
        return $this->json([
            'data' => $this->booking->findBy($filter),
        ]);
    }

    #[Route('/acceptRule', methods: ['POST'])]
    public function acceptRule(Request $request): JsonResponse
    {
        $orderId = $request->get('orderId');
        $isRuleAccepted = $request->get('isRuleAccepted');
        $token = $this->clientService->getTokenByOrigin($request->headers->get('origin', 'http://localhost'));
        $this->booking->setToken($token->getToken());
        $this->booking->acceptRule($orderId, $isRuleAccepted);

        return $this->json([
            'data' => 'ok',
        ]);
    }

    #[Route('/booking/{id<\d+>}/check-in', methods: ['PUT'])]
    public function checkIn(Request $request, int $id): JsonResponse
    {
        $checkIn = $request->get('checkIn');
        $token = $this->clientService->getTokenByOrigin($request->headers->get('origin', 'http://localhost'));
        $this->booking->setToken($token->getToken());
        $this->booking->setCheckInStatus($id, $checkIn);

        return $this->json([
            'data' => 'ok',
        ]);
    }

    #[Route('/booking/{id<\d+>}/photo', methods: ['POST'])]
    public function uploadPhoto(Request $request, int $id): JsonResponse
    {
        $token = $this->clientService->getTokenByOrigin($request->headers->get('origin', 'http://localhost'));
        $this->booking->setToken($token->getToken());
        $bookingDto = $this->booking->findById($id);
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

        $photo = $this->photoStorage->put($bookingDto, $file->getRealPath());
        $this->booking->addPhoto($id, $photo->getUrl());

        return $this->json([
            'data' => $photo->getId(),
        ]);
    }

    #[Route('/booking/{id<\d+>}/photo/{photoId<\d+>}', methods: ['DELETE'])]
    public function deletePhoto(Request $request, int $id, int $photoId): JsonResponse
    {
        $photo = $this->photoRepository->find($photoId);
        if (!$photo) {
            throw new NotFoundHttpException("Photo $photoId is not found");
        }

        $token = $this->clientService->getTokenByOrigin($request->headers->get('origin', 'http://localhost'));
        $this->booking->setToken($token->getToken());

        $this->photoStorage->remove($photo);
        $this->booking->removePhoto($id, $photo->getUrl());

        return $this->json([
            'data' => 'ok',
        ]);
    }

    #[Route('/booking/{id<\d+>}/guests', methods: ['PUT'])]
    public function updateGuests(Request $request): JsonResponse
    {
        $token = $this->clientService->getTokenByOrigin($request->headers->get('origin', 'http://localhost'));
        $this->booking->setToken($token->getToken());

        $bookingDto = new Booking(...$request->toArray());
        $this->booking->updateGuests($bookingDto);

        return $this->json([
            'data' => 'ok',
        ]);
    }

    #[Route('/booking/{id<\d+>}/pay-by-cash', methods: ['PUT'])]
    public function payByCash(Request $request, int $id): JsonResponse
    {
        $token = $this->clientService->getTokenByOrigin($request->headers->get('origin', 'http://localhost'));
        $this->booking->setToken($token->getToken());
        $this->booking->setPaidStatus($id, 'paid by cash');

        return $this->json([
            'data' => 'ok',
        ]);
    }

    private function isImage(UploadedFile $file): bool
    {
        return str_contains($file->getMimeType(), 'image');
    }
}
