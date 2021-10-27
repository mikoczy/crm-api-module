<?php

namespace Crm\ApiModule\Authorization;

use Crm\UsersModule\Repository\AccessTokensRepository;
use Crm\UsersModule\Repository\UsersRepository;
use Nette\Security\Authorizator;
use Nette\Security\User;

class AdminLoggedAuthorization implements ApiAuthorizationInterface
{
    private $accessTokensRepository;

    private $usersRepository;

    private $user;

    private $errorMessage = false;

    protected $authorizedData = [];

    public function __construct(
        User $user,
        AccessTokensRepository $accessTokensRepository,
        UsersRepository $usersRepository
    ) {
        $this->user = $user;
        $this->accessTokensRepository = $accessTokensRepository;
        $this->usersRepository = $usersRepository;
    }

    public function authorized($resource = Authorizator::ALL)
    {
        $userId = null;
        if ($this->user->isLoggedIn()) {
            $userId = $this->user->getId();
        } else {
            $tokenParser = new TokenParser();
            if (!$tokenParser->isOk()) {
                $this->errorMessage = $tokenParser->errorMessage();
                return false;
            }

            $token = $this->accessTokensRepository->loadToken($tokenParser->getToken());
            if (!$token) {
                $this->errorMessage = "Token doesn't exists";
                return false;
            }

            $userId = $token->user_id;
        }

        if (!$this->usersRepository->isRole($userId, UsersRepository::ROLE_ADMIN)) {
            $this->errorMessage = 'User not admin';
            return false;
        }

        return true;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getAuthorizedData()
    {
        return [];
    }
}
