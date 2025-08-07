<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Client;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ClientVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT'])
            && $subject instanceof Client;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $admin = $token->getUser();

        if (!$admin || !$subject instanceof Client) {
            return false;
        }

        return $subject->getAdmin()?->getId() === $admin->getId();
    }
}
