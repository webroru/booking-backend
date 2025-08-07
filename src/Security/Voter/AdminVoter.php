<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Admin;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdminVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT'])
            && $subject instanceof Admin;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $admin = $token->getUser();

        if (!$admin || !$subject instanceof Admin) {
            return false;
        }

        return $subject?->getId() === $admin->getId();
    }
}
