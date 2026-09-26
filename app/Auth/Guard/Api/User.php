<?php

declare(strict_types=1);

namespace App\Auth\Guard\Api;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $keyType = 'string';

    public string $id;

    public string $name;

    public string $email;

    public function getAuthIdentifier(): string
    {
        return $this->id;
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }
}
