<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Room;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RoomVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT'])
            && $subject instanceof Room;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $admin = $token->getUser();

        if (!$admin || !$subject instanceof Room) {
            return false;
        }

        return $subject->getClient()?->getAdmin()?->getId() === $admin->getId();
    }
}
