<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Maarheeze\Compass\Database\Factories\UserFactory;

/**
 * @property ?string $avatar_url
 * @property CarbonImmutable $created_at
 * @property ?string $email
 * @property string $github_id
 * @property string $github_token
 * @property int $id
 * @property ?string $name
 * @property string $nickname
 * @property ?string $remember_token
 * @property CarbonImmutable $updated_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * @return list<string>
     */
    public function getFillable(): array
    {
        return [
            'github_id',
            'nickname',
            'name',
            'email',
            'avatar_url',
            'github_token',
        ];
    }

    /**
     * @return list<string>
     */
    public function getHidden(): array
    {
        return [
            'github_token',
            'remember_token',
        ];
    }

    public function getTable(): string
    {
        return 'compass_users';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'github_token' => 'encrypted',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
