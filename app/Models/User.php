<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    public const ROLE_ADMINISTRADOR = 'administrador';

    public const ROLE_MEDICO = 'medico';

    public const ROLE_RECEPCION = 'recepcion';

    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasRole(string $role): bool
    {
        $requestedRole = strtolower(trim($role));
        $currentRole = strtolower((string) ($this->role ?? ''));

        return $currentRole === $requestedRole;
    }

    public static function rolePermissions(?string $role): array
    {
        $roleKey = strtolower(trim((string) $role));

        return config('roles.'.$roleKey.'.permissions', []);
    }

    public function permissions(): array
    {
        return self::rolePermissions($this->role);
    }

    public function hasPermission(string $permission): bool
    {
        $requestedPermission = strtolower(trim($permission));

        return in_array($requestedPermission, $this->permissions(), true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMINISTRADOR);
    }

    public function isMedico(): bool
    {
        return $this->hasRole(self::ROLE_MEDICO);
    }

    public function isRecepcion(): bool
    {
        return $this->hasRole(self::ROLE_RECEPCION);
    }
}
