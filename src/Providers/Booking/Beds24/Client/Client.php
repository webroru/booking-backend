<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Client;

use App\Entity\Beds24Token;
use App\Providers\Booking\Beds24\Dto\Response;
use App\Providers\Booking\Beds24\Dto\Request;
use App\Providers\Booking\Beds24\Exception\EmptyUrlException;
use App\Providers\Booking\Beds24\Exception\IncorrectMethodException;
use App\Providers\Booking\Beds24\Exception\ResponseDtoNotFoundException;
use App\Repository\TokenRepository;
use App\Service\ClientService;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\RequestOptions;

/**
 * @method Response\GetBookingsDto getBookings(Request\GetBookingsDto $bookingsDto)
 * @method Response\GetAuthenticationSetupDto getAuthenticationSetup()
 * @method Response\GetAuthenticationTokenDto getAuthenticationToken()
 * @method Response\GetPropertiesDto getProperties(Request\GetPropertiesDto $getPropertiesDto)
 * @method Response\PostBookingsDto postBookings(Request\PostBookingsDto $postBookingsDto)
 */
class Client
{
    private const HOST = 'https://api.beds24.com';
    private const API_VERSION = 'v2';
    private ?string $code = null;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly ClientService $clientService,
        private readonly TokenRepository $tokenRepository,
    ) {
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function __call(string $name, array $arguments): object
    {
        $pieces = preg_split('/(?=[A-Z])/', $name);
        $method = $pieces[0];
        if (!$this->isMethodValid($method)) {
            throw new IncorrectMethodException("$method is not a valid method");
        };
        $pathParts = array_slice($pieces, 1);
        $pathParts = array_map(fn(string $item) => strtolower($item), $pathParts);
        if (count($pathParts) === 0) {
            throw new EmptyUrlException("Endpoint is not specified");
        }
        $path = implode('/', $pathParts);
        $url = self::HOST . '/' . self::API_VERSION . '/' . $path;

        $token = $this->tokenRepository->findOneByClient($this->clientService->getClient());

        if ($path !== 'authentication/token' && $token->getExpiresAt() <= (new \DateTime())) {
            $this->refreshToken($token);
        }

        $headers = $path === 'authentication/token' ? [
            'refreshToken' => $token?->getRefreshToken(),
            'code' => $this->code,
        ] : [
            'token' => $token?->getToken(),
        ];

        $options = $this->makeOptions($method, $arguments, $headers);

        $response = $this->client->request(
            $method,
            $url,
            $options,
        );
        $data = json_decode($response->getBody()->getContents(), true);

        return $this->responseDtoFactory(ucfirst("{$name}Dto"), $data);
    }

    private function isMethodValid(string $method): bool
    {
        return in_array($method, ['get', 'post', 'put', 'path', 'update', 'delete']);
    }

    private function makeOptions(string $method, array $data, array $headers): array
    {
        $options[RequestOptions::HEADERS] = $headers;

        if (isset($data[0]) && $data[0] instanceof Request\RequestDtoInterface) {
            $requestData = $data[0]->toArray();
            $requestData = array_filter($requestData);
            if ($method === 'get') {
                $options[RequestOptions::QUERY] = Query::build($requestData);
            }

            if ($method === 'post') {
                $options[RequestOptions::JSON] = $requestData;
            }
        }

        return $options;
    }

    private function responseDtoFactory(string $name, array $data): object
    {
        $namespace = 'App\Providers\Booking\Beds24\Dto\Response';
        $class = "$namespace\\$name";
        if (!class_exists("$namespace\\$name")) {
            throw new ResponseDtoNotFoundException("$class is not found");
        }

        return new $class(...$data);
    }

    private function refreshToken(Beds24Token $token): void
    {
        $tokenDto = $this->getAuthenticationToken();
        $token
            ->setToken($tokenDto->token)
            ->setExpiresAt(new \DateTime("+ $tokenDto->expiresIn seconds"))
        ;
        $this->tokenRepository->save($token, true);
    }
}
