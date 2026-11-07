<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserIsActive;
use App\Models\Product;
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
        $this->get('/products')->assertRedirect('/login');
    }

    public function test_three_demo_accounts_can_login_and_see_role_specific_sidebar(): void
    {
        $admin = User::where('email', 'admin@inventory.test')->firstOrFail();
        $staff = User::where('email', 'staff@inventory.test')->firstOrFail();
        $pimpinan = User::where('email', 'pimpinan@inventory.test')->firstOrFail();

        // Admin melihat seluruh menu termasuk Pengguna, Laporan, dan Kategori
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

        // Pimpinan melihat menu Barang Masuk, Barang Keluar, Kartu Stok, & Laporan, tanpa Pengguna & Kategori
        $this->actingAs($pimpinan)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Barang Masuk')
            ->assertSee('Barang Keluar')
            ->assertSee('Kartu Stok')
            ->assertSee('Laporan')
            ->assertDontSee(route('users.index'))
            ->assertDontSee(route('categories.index'));
    }

    public function test_role_authorization_rules(): void
    {
        $admin = User::where('email', 'admin@inventory.test')->firstOrFail();
        $staff = User::where('email', 'staff@inventory.test')->firstOrFail();
        $pimpinan = User::where('email', 'pimpinan@inventory.test')->firstOrFail();
        $product = Product::firstOrFail();

        // TC-30: Staff membuka /users -> 403
        $this->actingAs($staff)->get('/users')->assertStatus(403);

        // TC-31: Pimpinan membuka /stock-ins/create & /stock-outs/create -> 403
        $this->actingAs($pimpinan)->get('/stock-ins/create')->assertStatus(403);
        $this->actingAs($pimpinan)->get('/stock-outs/create')->assertStatus(403);

        // Pimpinan dapat melihat daftar transaksi masuk & keluar (hanya baca) -> 200
        $this->actingAs($pimpinan)->get('/stock-ins')->assertOk();
        $this->actingAs($pimpinan)->get('/stock-outs')->assertOk();

        // TC-32: Pimpinan membuka /products/create, edit, atau master lainnya -> 403
        $this->actingAs($pimpinan)->get('/products/create')->assertStatus(403);
        $this->actingAs($pimpinan)->get(route('products.edit', $product))->assertStatus(403);
        $this->actingAs($pimpinan)->delete(route('products.destroy', $product))->assertStatus(403);
        $this->actingAs($pimpinan)->get('/categories')->assertStatus(403);
        $this->actingAs($pimpinan)->get('/units')->assertStatus(403);
        $this->actingAs($pimpinan)->get('/suppliers')->assertStatus(403);

        // Pimpinan hanya bisa melihat daftar dan detail barang -> 200
        $this->actingAs($pimpinan)
            ->get('/products')
            ->assertOk()
            ->assertDontSee('Tambah Barang');

        $this->actingAs($pimpinan)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($product->nama);

        // TC-33: Staff membuka /reports/stok -> 403
        $this->actingAs($staff)->get('/reports/stok')->assertStatus(403);

        // Pimpinan bisa membuka /reports/stok -> 200
        $this->actingAs($pimpinan)->get('/reports/stok')->assertOk();

        // Admin bisa membuka /users -> 200
        $this->actingAs($admin)->get('/users')->assertOk();
    }

    public function test_admin_cannot_delete_deactivate_or_demote_self_and_must_keep_at_least_one_active_admin(): void
    {
        $admin = User::where('email', 'admin@inventory.test')->firstOrFail();

        // Buat admin aktif kedua agar pengujian menolak aksi pada diri sendiri murni karena aturan self-protection
        $secondAdmin = User::factory()->create([
            'name' => 'Admin Kedua',
            'email' => 'admin2@inventory.test',
            'is_active' => true,
        ]);
        $secondAdmin->assignRole('admin');

        // 1a. Admin mencoba menghapus akunnya sendiri lewat request langsung ke server -> ditolak
        $this->actingAs($admin)
            ->from(route('users.index'))
            ->delete(route('users.destroy', $admin))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'Anda tidak dapat menonaktifkan atau menghapus akun Anda sendiri.');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_active' => true]);

        // 1b. Admin mencoba menonaktifkan akunnya sendiri -> ditolak
        $this->actingAs($admin)
            ->from(route('users.edit', $admin))
            ->put(route('users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'admin',
                'is_active' => '0',
            ])
            ->assertRedirect(route('users.edit', $admin))
            ->assertSessionHas('error', 'Anda tidak dapat menonaktifkan atau menghapus akun Anda sendiri.');

        $this->assertTrue($admin->fresh()->is_active);

        // 1c. Admin mencoba menurunkan perannya sendiri menjadi staff -> ditolak
        $this->actingAs($admin)
            ->from(route('users.edit', $admin))
            ->put(route('users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'staff',
                'is_active' => '1',
            ])
            ->assertRedirect(route('users.edit', $admin))
            ->assertSessionHas('error', 'Anda tidak dapat menonaktifkan atau menghapus akun Anda sendiri.');

        $this->assertTrue($admin->fresh()->hasRole('admin'));

        // 2. Uji proteksi server "minimal satu admin aktif" pada UserController:
        // Misalkan secondAdmin berstatus nonaktif di DB, lalu mencoba menonaktifkan/menurunkan/menghapus satu-satunya admin aktif ($admin)
        $secondAdmin->update(['is_active' => false]);

        $this->withoutMiddleware(EnsureUserIsActive::class)
            ->actingAs($secondAdmin)
            ->from(route('users.edit', $admin))
            ->put(route('users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'admin',
                'is_active' => '0',
            ])
            ->assertRedirect(route('users.edit', $admin))
            ->assertSessionHas('error', 'Sistem harus memiliki minimal satu admin aktif.');

        $this->assertTrue($admin->fresh()->is_active);

        $this->withoutMiddleware(EnsureUserIsActive::class)
            ->actingAs($secondAdmin)
            ->from(route('users.edit', $admin))
            ->put(route('users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'staff',
                'is_active' => '1',
            ])
            ->assertRedirect(route('users.edit', $admin))
            ->assertSessionHas('error', 'Sistem harus memiliki minimal satu admin aktif.');

        $this->assertTrue($admin->fresh()->hasRole('admin'));

        $this->withoutMiddleware(EnsureUserIsActive::class)
            ->actingAs($secondAdmin)
            ->from(route('users.index'))
            ->delete(route('users.destroy', $admin))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'Sistem harus memiliki minimal satu admin aktif.');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_active' => true]);
    }
}
