<?php

declare(strict_types=1);

namespace App\Providers\Booking\Beds24\Dto\Response;

class GetAuthenticationSetupDto
{
    public function __construct(
        public readonly string $token,
        public readonly int $expiresIn,
        public readonly string $refreshToken,
    ) {}
}
