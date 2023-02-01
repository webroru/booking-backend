<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api', name: 'api_info')]
class InfoController extends AbstractController
{
    public function __construct(
        private readonly ClientRepository $clientRepository,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    #[Route('/info', methods: ['GET'])]
    public function index(Request $request,): JsonResponse
    {
        $origin = $request->headers->get('origin', 'http://localhost');
        $domain = parse_url($origin, PHP_URL_HOST);
        $client = $this->clientRepository->findOneBy(['domain' => $domain]);
        if (!$client) {
            throw new \Exception("Request from $domain is not allowed");
        }

        return $this->json(['data' => $this->normalizer->normalize($client->getInfo(), null, [AbstractNormalizer::IGNORED_ATTRIBUTES => ['__initializer__', '__cloner__', '__isInitialized__']])]);
    }
}
