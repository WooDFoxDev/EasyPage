<?php

namespace Easypage\Kernel;

use Easypage\Models\UserModel;

class Authenticator
{
    static public function logIn(string $username, string $password): bool
    {
        if (!$user = UserModel::findSingular(['username' => $username])) {
            return false;
        }

        if (!$user->checkPassword($password)) {
            return false;
        }

        self::setUserToSession($user);

        return true;
    }

    static public function isLoggedIn(): bool
    {
        if (!$user_authenticated = self::getUserFromSession()) {
            return false;
        }

        if (!$user = UserModel::findById($user_authenticated->getId())) {
            self::logOut();

            return false;
        }

        if ($user->getRevokeSequence() != $user_authenticated->getRevokeSequence()) {
            self::logOut();

            return false;
        }

        self::setUserToSession($user);

        return true;
    }

    static private function getUserFromSession(): UserModel|false
    {
        $authenticated = Core::getInstance()->getSession()->get('authenticated');

        if (is_null($authenticated)) {
            return false;
        }

        return new UserModel($authenticated);
    }

    static private function setUserToSession(UserModel $user): void
    {
        Core::getInstance()->getSession()->set('authenticated', $user->export());
    }

    static public function logOut(): void
    {
        Core::getInstance()->getSession()->destroy();
    }
}
