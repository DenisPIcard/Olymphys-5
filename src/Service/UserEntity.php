<?php

namespace App\Service;


use League\OAuth2\Server\Entities\Interfaces\UserEntityInterface;
use App\Entity\User;


class UserEntity implements UserEntityInterface
{
    private $username;

    /**
     * Return the user's identifier.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return (string)$this->username;


    }


}