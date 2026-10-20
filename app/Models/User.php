<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'phone',
        'password',
        'role',
        'roles',
        'is_active',
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
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
            'roles' => 'array',
        ];
    }

    public function scopeWhereHasRole(Builder $query, string $role): Builder
    {
        return $query->where(function (Builder $query) use ($role) {
            $query->where('role', $role)
                ->orWhereJsonContains('roles', $role);
        });
    }

    public function assignedRoles(): array
    {
        $roles = $this->roles;

        return is_array($roles) && $roles !== []
            ? array_values(array_unique($roles))
            : array_filter([(string) ($this->role ?? '')]);
    }

    public function hasRole(string $role): bool
    {
        $requestedRole = strtolower(trim($role));

        return in_array($requestedRole, array_map(
            fn (string $assignedRole) => strtolower(trim($assignedRole)),
            $this->assignedRoles()
        ), true);
    }

    public static function rolePermissions(?string $role): array
    {
        $roleKey = strtolower(trim((string) $role));

        return config('roles.'.$roleKey.'.permissions', []);
    }

    public function permissions(): array
    {
        if (! $this->isActive()) {
            return [];
        }

        return collect($this->assignedRoles())
            ->flatMap(fn (string $role) => self::rolePermissions($role))
            ->unique()
            ->values()
            ->all();
    }

    public function hasPermission(string $permission): bool
    {
        $requestedPermission = strtolower(trim($permission));

        return in_array($requestedPermission, $this->permissions(), true);
    }

    public function availablePanels(): array
    {
        if (! $this->isActive()) {
            return [];
        }

        return collect($this->assignedRoles())
            ->flatMap(fn (string $role) => config('roles.'.strtolower($role).'.panels', []))
            ->filter(fn (array $panel) => ! isset($panel['permission']) || $this->hasPermission($panel['permission']))
            ->unique(fn (array $panel) => $panel['key'] ?? $panel['route'] ?? '')
            ->values()
            ->all();
    }

    public function canAccessPanel(string $panelKey): bool
    {
        return collect($this->availablePanels())
            ->contains(fn (array $panel) => ($panel['key'] ?? $panel['route'] ?? '') === $panelKey);
    }

    public function defaultDashboardRoute(): ?string
    {
        return match (strtolower($this->assignedRoles()[0] ?? '')) {
            self::ROLE_ADMINISTRADOR => 'admin.panel',
            self::ROLE_MEDICO => 'medico.dashboard',
            self::ROLE_RECEPCION => 'recepcion.dashboard',
            default => null,
        };
    }

    public function isActive(): bool
    {
        return (bool) ($this->is_active ?? true);
    }

    public function statusLabel(): string
    {
        return $this->isActive() ? 'Activo' : 'Inactivo';
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMINISTRADOR);
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin;
    }

    public function isMedico(): bool
    {
        return $this->hasRole(self::ROLE_MEDICO);
    }

    public function isRecepcion(): bool
    {
        return $this->hasRole(self::ROLE_RECEPCION);
    }

    public function doctorProfile(): HasOne
    {
        return $this->hasOne(DoctorProfile::class);
    }
}
