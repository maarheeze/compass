<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Maarheeze\Compass\Database\Factories\UserFactory;

#[Fillable([
    'github_id',
    'nickname',
    'name',
    'email',
    'avatar_url',
    'github_token',
])]
#[Hidden(['github_token', 'remember_token'])]
#[Table('compass_users')]
#[UseFactory(UserFactory::class)]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'github_token' => 'encrypted',
        ];
    }
}
