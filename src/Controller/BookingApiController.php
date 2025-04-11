<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\BookingDto;
use App\Dto\GuestDto;
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
        return $this->json($this->booking->findBy($filter));
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

        return $this->json($photo->getId());
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

        return $this->json([]);
    }

    #[Route('/booking/{id<\d+>}/message', methods: ['POST'])]
    public function message(Request $request, int $id): JsonResponse
    {
        $message = $request->get('message');
        $this->booking->sendMessage($id, $message);

        return $this->json([]);
    }

    #[Route('/booking/{id<\d+>}', methods: ['PUT'])]
    public function update(Request $request): JsonResponse
    {
        $bookingDto = $this->bookingDtoFromArray($request->toArray());
        $this->booking->updateBooking($bookingDto);

        return $this->json([]);
    }

    private function isImage(UploadedFile $file): bool
    {
        return str_contains($file->getMimeType(), 'image');
    }

    private function bookingDtoFromArray(array $data): BookingDto
    {
        $guests = array_map(fn(array $guest) => new GuestDto(
            id: $guest['id'] ?? null,
            firstName: $guest['firstName'],
            lastName: $guest['lastName'],
            documentNumber: $guest['documentNumber'],
            documentType: $guest['documentType'],
            gender: $guest['gender'] ?? null,
            dateOfBirth: $guest['dateOfBirth'],
            nationality: $guest['nationality'],
        ), $data['guests']);

        return new BookingDto(...[...$data, 'guests' => $guests]);
    }
}
