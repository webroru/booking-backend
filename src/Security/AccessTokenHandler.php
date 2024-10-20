<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\AdminRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private readonly AdminRepository $repository
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        // e.g. query the "access token" database to search for this token
        $admin = $this->repository->findOneByToken($accessToken);
        if (!$admin) {
            throw new BadCredentialsException('Invalid credentials.');
        }

        return new UserBadge($admin->getUserIdentifier());
    }
}
