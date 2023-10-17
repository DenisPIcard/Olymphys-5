<?php

namespace App\Security;

use App\Entity\User as AppUser;
use Exception;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof AppUser) {
            return;
        }
    }

    /**
     * @throws Exception
     */
    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof AppUser) {
            return;
        }

        // user account is expired, the user may be notified
        if (!$user->getIsActive()) {

            throw new Exception("Ce membre n'est pas actif. Soit vous n'avez pas terminé la procédure d'inscription, et vous ne pouvez donc pas vous connecter
    soit votre compte a été inactivé par un des webmestres. Contactez nous");
        }
    }
}
