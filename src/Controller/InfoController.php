<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\InfoNotFoundException;
use App\Serializer\Normalizer;
use App\Service\ClientService;
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
    ) {
    }

    #[Route('/info', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $client = $this->clientService->getClientByOrigin($request->headers->get('origin', 'http://localhost'));
        $info = $client->getInfo() ?? throw new InfoNotFoundException("Information for {$client->getName()} not found");

        return $this->json(['data' => $this->normalizer->normalize($info)]);
    }
}
