<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\BookingDto;
use App\Providers\Booking\BookingInterface;
use App\Providers\PhotoStorage\Local\Local;
use App\Repository\PhotoRepository;
use App\Service\ClientService;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_booking')]
class BookingApiController extends AbstractApiController
{
    public function __construct(
        private readonly BookingInterface $booking,
        ClientService $clientService,
        private readonly PhotoRepository $photoRepository,
        private readonly Local $photoStorage,
        RequestStack $requestStack,
    ) {
        parent::__construct($clientService, $requestStack);
    }

    #[Route('/booking', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $filter = $request->query->all();
        return $this->json([
            'data' => $this->booking->findBy($filter),
        ]);
    }

    #[Route('/acceptRule', methods: ['POST'])]
    public function acceptRule(Request $request): JsonResponse
    {
        $orderId = $request->get('orderId');
        $isRuleAccepted = $request->get('isRuleAccepted');
        $this->booking->acceptRule($orderId, $isRuleAccepted);

        return $this->json([
            'data' => 'ok',
        ]);
    }

    #[Route('/booking/{id<\d+>}/check-in', methods: ['PUT'])]
    public function checkIn(Request $request, int $id): JsonResponse
    {
        $checkIn = $request->get('checkIn');
        $this->booking->setCheckInStatus($id, $checkIn);

        return $this->json([
            'data' => 'ok',
        ]);
    }

    #[Route('/booking/{id<\d+>}/check-out', methods: ['PUT'])]
    public function checkOut(Request $request, int $id): JsonResponse
    {
        $this->booking->setCheckOutStatus($id);

        return $this->json([
            'data' => 'ok',
        ]);
    }

    #[Route('/booking/{id<\d+>}/photo', methods: ['POST'])]
    public function uploadPhoto(Request $request, int $id): JsonResponse
    {
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

        $this->photoStorage->remove($photo);
        $this->booking->removePhoto($id, $photo->getUrl());

        return $this->json([
            'data' => 'ok',
        ]);
    }

    #[Route('/booking/{id<\d+>}/guests', methods: ['PUT'])]
    public function updateGuests(Request $request): JsonResponse
    {
        $bookingDto = new BookingDto(...$request->toArray());
        $this->booking->updateGuests($bookingDto);

        return $this->json([
            'data' => $this->booking->findById($bookingDto->orderId),
        ]);
    }

    #[Route('/booking/{id<\d+>}/pay-by-cash', methods: ['PUT'])]
    public function payByCash(Request $request, int $id): JsonResponse
    {
        $isPayByCash = $request->get('isPayByCash');
        $this->booking->setPaidStatus($id, $isPayByCash ? 'paid by cash' : '');

        return $this->json([
            'data' => 'ok',
        ]);
    }

    #[Route('/booking/{id<\d+>}/cancel', methods: ['PUT'])]
    public function cancel(Request $request, int $id): JsonResponse
    {
        $this->booking->cancel($id);

        return $this->json([
            'data' => 'ok',
        ]);
    }

    #[Route('/booking/{id<\d+>}/message', methods: ['POST'])]
    public function message(Request $request, int $id): JsonResponse
    {
        $message = $request->get('message');
        $this->booking->sendMessage($id, $message);

        return $this->json([
            'data' => 'ok',
        ]);
    }

    private function isImage(UploadedFile $file): bool
    {
        return str_contains($file->getMimeType(), 'image');
    }
}
