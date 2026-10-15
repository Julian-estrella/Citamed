<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_protected_super_admin(): void
    {
        $this->seed();

        $superAdmin = User::where('email', 'admin@admin.com')->firstOrFail();

        $this->assertSame('Super admin', $superAdmin->name);
        $this->assertSame('administrador', $superAdmin->role);
        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertTrue(Hash::check('Aa#12345', $superAdmin->password));
    }

    public function test_admins_cannot_delete_the_super_admin_from_user_management(): void
    {
        $superAdmin = User::factory()->create([
            'name' => 'Super admin',
            'email' => 'admin@admin.com',
            'role' => 'administrador',
            'is_super_admin' => true,
        ]);
        $otherAdmin = User::factory()->create(['role' => 'administrador']);

        $this->actingAs($otherAdmin)
            ->delete(route('admin.users.destroy', $superAdmin))
            ->assertRedirect(route('admin.users'))
            ->assertSessionHas('error');

        $this->assertNotNull($superAdmin->fresh());

        $this->actingAs($superAdmin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertDontSee('Eliminar usuario Super admin');

        $this->actingAs($superAdmin)
            ->delete(route('admin.users.destroy', $superAdmin))
            ->assertRedirect(route('admin.users'))
            ->assertSessionHas('error');

        $this->assertNotNull($superAdmin->fresh());
    }

    public function test_admins_cannot_deactivate_the_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'administrador',
            'is_super_admin' => true,
            'is_active' => true,
        ]);
        $admin = User::factory()->create(['role' => 'administrador']);

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $superAdmin))
            ->assertOk()
            ->assertSee('select disabled', false)
            ->assertSee('name="is_active" value="1"', false);

        $this->actingAs($admin)
            ->patch(route('admin.users.toggle-status', $superAdmin))
            ->assertRedirect(route('admin.users'))
            ->assertSessionHas('error', 'No puedes desactivar al super administrador.');

        $this->assertTrue($superAdmin->fresh()->isActive());

        $this->put(route('admin.users.update', $superAdmin), [
            'name' => $superAdmin->name,
            'email' => $superAdmin->email,
            'role' => $superAdmin->role,
            'is_active' => '0',
        ])->assertRedirect(route('admin.users'))
            ->assertSessionHas('error', 'No puedes desactivar al super administrador.');

        $this->assertTrue($superAdmin->fresh()->isActive());
    }
}