<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Guest;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GuestVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT'])
            && $subject instanceof Guest;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $admin = $token->getUser();

        if (!$admin || !$subject instanceof Guest) {
            return false;
        }

        return $subject->getClient()?->getAdmin()?->getId() === $admin->getId();
    }
}
