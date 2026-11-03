<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/users')->assertRedirect('/login');
    }

    public function test_three_demo_accounts_can_login_and_see_role_specific_sidebar(): void
    {
        $admin = User::where('email', 'admin@inventory.test')->firstOrFail();
        $staff = User::where('email', 'staff@inventory.test')->firstOrFail();
        $pimpinan = User::where('email', 'pimpinan@inventory.test')->firstOrFail();

        // Admin melihat menu Pengguna, Laporan, dan Kategori
        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Pengguna')
            ->assertSee('Laporan')
            ->assertSee('Kategori');

        // Staff melihat menu Kategori & Barang Masuk, tetapi tidak melihat Pengguna & Laporan
        $this->actingAs($staff)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Kategori')
            ->assertSee('Barang Masuk')
            ->assertDontSee(route('users.index'))
            ->assertDontSee(route('reports.show', 'stok'));

        // Pimpinan melihat menu Laporan & Kartu Stok, tetapi tidak melihat Pengguna & Kategori
        $this->actingAs($pimpinan)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Laporan')
            ->assertSee('Kartu Stok')
            ->assertDontSee(route('users.index'))
            ->assertDontSee(route('categories.index'));
    }

    public function test_role_authorization_rules(): void
    {
        $admin = User::where('email', 'admin@inventory.test')->firstOrFail();
        $staff = User::where('email', 'staff@inventory.test')->firstOrFail();
        $pimpinan = User::where('email', 'pimpinan@inventory.test')->firstOrFail();

        // TC-30: Staff membuka /users -> 403
        $this->actingAs($staff)->get('/users')->assertStatus(403);

        // TC-31: Pimpinan membuka /stock-ins/create -> 403
        $this->actingAs($pimpinan)->get('/stock-ins/create')->assertStatus(403);

        // TC-32: Pimpinan membuka /products/create -> 403
        $this->actingAs($pimpinan)->get('/products/create')->assertStatus(403);

        // TC-33: Staff membuka /reports/stok -> 403
        $this->actingAs($staff)->get('/reports/stok')->assertStatus(403);

        // Pimpinan bisa membuka /reports/stok -> 200
        $this->actingAs($pimpinan)->get('/reports/stok')->assertOk();

        // Admin bisa membuka /users -> 200
        $this->actingAs($admin)->get('/users')->assertOk();
    }
}
