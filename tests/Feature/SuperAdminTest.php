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
}