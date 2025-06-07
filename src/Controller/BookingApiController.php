<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\BookingDto;
use App\Dto\GuestDto;
use App\Providers\Booking\BookingInterface;
use App\Service\ClientService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_booking')]
class BookingApiController extends AbstractApiController
{
    public function __construct(
        private readonly BookingInterface $booking,
        ClientService $clientService,
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

    private function bookingDtoFromArray(array $data): BookingDto
    {
        $guests = array_map(fn(array $guest) => new GuestDto(...$guest), $data['guests']);

        return new BookingDto(...[...$data, 'guests' => $guests]);
    }
}
