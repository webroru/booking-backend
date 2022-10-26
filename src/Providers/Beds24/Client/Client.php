<?php

declare(strict_types=1);

namespace App\Providers\Beds24\Client;

use App\Providers\Beds24\Dto\Request\GetBookingsDto;
use App\Providers\Beds24\Dto\Response\GetAuthenticationSetupDto;
use App\Providers\Beds24\Exception\EmptyUrlException;
use App\Providers\Beds24\Exception\IncorrectMethodException;
use App\Providers\Beds24\Exception\ResponseDtoNotFoundException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

/**
 * @method string getBookings(GetBookingsDto $bookingsDto)
 * @method GetAuthenticationSetupDto getAuthenticationSetup()
 * @method string getAuthenticationToken()
 */
class Client
{
    private const HOST = 'https://api.beds24.com';
    private const API_VERSION = 'v2';
    private ?string $token = null;
    private ?string $refreshToken = null;
    private ?string $code = null;

    public function __construct(
        private readonly ClientInterface $client,
    ) {}

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
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

        $options = $this->makeOptions($method, $arguments);

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

    private function makeOptions(string $method, array $data): array
    {
        $options = [];
        if (isset($arguments[0])) {
            if ($method === 'get') {
                $options[RequestOptions::QUERY] = $data[0];
            }

            if ($method === 'post') {
                $options[RequestOptions::JSON] = $data[0];
            }
        }

        $options[RequestOptions::HEADERS] = [
            'token' => $this->token,
            'refreshToken' => $this->refreshToken,
            'code' => $this->code,
        ];

        return $options;
    }

    private function responseDtoFactory(string $name, array $data): object
    {
        $namespace = 'App\Providers\Beds24\Dto\Response';
        $class = "$namespace\\$name";
        if (!class_exists("$namespace\\$name")) {
            throw new ResponseDtoNotFoundException("$class is not found");
        }

        return new $class(...$data);
    }
}
