<?php

namespace Easypage\Models;

use Easypage\Kernel\Abstractions\Model;

class UserModel extends Model
{
    protected static string $repository = 'users';
    protected array $persistent = ['username', 'password_hash', 'revoke_sequence'];

    protected string $username;
    protected string $password_hash;
    protected ?int $revoke_sequence = null;

    protected string $password;
    protected string $password_confirm;

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRevokeSequence(): int
    {
        return $this->revoke_sequence;
    }

    private function hashPassword(): void
    {
        $this->password_hash = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function checkPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }

    protected function onExport(): bool
    {
        if (isset($this->password)) {
            $this->hashPassword();
        }

        if (is_null($this->revoke_sequence)) {
            $this->revoke_sequence = 0;
        }

        return true;
    }

    protected function validate(): bool
    {
        $this->_is_valid = true;
        $this->_validator_messages = [];

        if ($this->validateProperty('username', 'hasPresence', invalidMessage: "Username cannot be blank")) {
            $this->validateProperty('username', 'hasLength', args: ['min' => 4, 'max' => 255], invalidMessage: "Username must be between 4 and 255 characters");
        }


        if (is_null($this->id) || isset($this->password) || empty($this->password_hash)) {
            if ($this->validateProperty('password', 'hasPresence', invalidMessage: "Password cannot be blank")) {
                $this->validateProperty('password', 'hasLength', args: ['min' => 4], invalidMessage: "Password must contain 4 or more characters");
            }

            if ($this->validateProperty('password_confirm', 'hasPresence', invalidMessage: "Confirm password cannot be blank")) {
                $this->validateProperty('password_confirm', 'isEqual', args: [$this->password], invalidMessage: "Password and confirm password must match");
            }
        }

        return $this->_is_valid;
    }
}
