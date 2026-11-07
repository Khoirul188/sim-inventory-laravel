<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->staff = User::where('email', 'staff@inventory.test')->firstOrFail();
    }

    public function test_category_crud_and_cannot_delete_used_category(): void
    {
        // Buat kategori baru
        $this->actingAs($this->staff)
            ->post(route('categories.store'), [
                'nama' => 'Kategori Uji Baru',
                'deskripsi' => 'Deskripsi kategori uji',
            ])
            ->assertRedirect(route('categories.index'));

        $category = Category::where('nama', 'Kategori Uji Baru')->firstOrFail();

        // Ubah kategori
        $this->actingAs($this->staff)
            ->put(route('categories.update', $category), [
                'nama' => 'Kategori Uji Diperbarui',
                'deskripsi' => 'Deskripsi diperbarui',
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'nama' => 'Kategori Uji Diperbarui']);

        // Hapus kategori yang belum dipakai -> berhasil
        $this->actingAs($this->staff)
            ->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);

        // TC-42: Hapus kategori yang dipakai barang -> ditolak
        $usedCategory = Category::has('products')->firstOrFail();
        $this->actingAs($this->staff)
            ->from(route('categories.index'))
            ->delete(route('categories.destroy', $usedCategory))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $usedCategory->id]);
    }

    public function test_unit_crud_and_cannot_delete_used_unit(): void
    {
        $this->actingAs($this->staff)
            ->post(route('units.store'), [
                'nama' => 'Lusin',
                'singkatan' => 'lsn',
            ])
            ->assertRedirect(route('units.index'));

        $unit = Unit::where('singkatan', 'lsn')->firstOrFail();

        $this->actingAs($this->staff)
            ->put(route('units.update', $unit), [
                'nama' => 'Lusin Baru',
                'singkatan' => 'lsn',
            ])
            ->assertRedirect(route('units.index'));

        $this->assertDatabaseHas('units', ['id' => $unit->id, 'nama' => 'Lusin Baru']);

        // Hapus satuan yang belum dipakai -> berhasil
        $this->actingAs($this->staff)
            ->delete(route('units.destroy', $unit))
            ->assertRedirect(route('units.index'));

        $this->assertDatabaseMissing('units', ['id' => $unit->id]);

        // Hapus satuan yang dipakai barang -> ditolak
        $usedUnit = Unit::has('products')->firstOrFail();
        $this->actingAs($this->staff)
            ->delete(route('units.destroy', $usedUnit))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('units', ['id' => $usedUnit->id]);
    }

    public function test_supplier_crud(): void
    {
        $this->actingAs($this->staff)
            ->post(route('suppliers.store'), [
                'nama' => 'PT Pemasok Nusantara',
                'telepon' => '(021) 555-1234',
                'email' => 'kontak@pemasok.co.id',
                'alamat' => 'Jl. Industri No. 10, Jakarta',
            ])
            ->assertRedirect(route('suppliers.index'));

        $supplier = Supplier::where('nama', 'PT Pemasok Nusantara')->firstOrFail();

        $this->actingAs($this->staff)
            ->put(route('suppliers.update', $supplier), [
                'nama' => 'PT Pemasok Nusantara Utama',
                'telepon' => '0811-2233-4455',
                'email' => 'info@pemasok.co.id',
                'alamat' => 'Jl. Industri No. 12, Jakarta',
            ])
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'nama' => 'PT Pemasok Nusantara Utama']);

        $this->actingAs($this->staff)
            ->delete(route('suppliers.destroy', $supplier))
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    public function test_product_auto_code_photo_upload_duplicate_code_and_soft_delete(): void
    {
        Storage::fake('public');

        $category = Category::firstOrFail();
        $unit = Unit::firstOrFail();

        // TC-40 & BR-13: Tambah barang tanpa kode -> otomatis BRG-0033 dan stok awal 0
        $response = $this->actingAs($this->staff)
            ->post(route('products.store'), [
                'kode_barang' => '',
                'nama' => 'Kursi Kerja Ergonomis',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'harga_beli' => 850000,
                'stok_minimum' => 3,
                'lokasi_rak' => 'G-01',
                'foto' => UploadedFile::fake()->image('kursi.jpg', 400, 400),
                'deskripsi' => 'Kursi kerja jaring hitam.',
            ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::where('nama', 'Kursi Kerja Ergonomis')->firstOrFail();
        $this->assertSame('BRG-0033', $product->kode_barang);
        $this->assertSame(0, $product->stok);
        $this->assertNotNull($product->foto);
        Storage::disk('public')->assertExists($product->foto);

        // TC-41: Tambah barang dengan kode yang sudah ada -> ditolak
        $this->actingAs($this->staff)
            ->post(route('products.store'), [
                'kode_barang' => 'BRG-0001',
                'nama' => 'Barang Duplikat',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'stok_minimum' => 5,
            ])
            ->assertSessionHasErrors([
                'kode_barang' => 'Kode barang sudah digunakan.',
            ]);

        // TC-43 & FR-16: Hapus barang -> soft delete
        $this->actingAs($this->staff)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_product_search_and_status_filter(): void
    {
        // TC-44: Filter barang status menipis
        $this->actingAs($this->staff)
            ->get(route('products.index', ['status' => 'menipis', 'search' => 'PaperOne']))
            ->assertOk()
            ->assertSee('Kertas HVS A4 80gsm PaperOne');
    }

    public function test_product_photo_upload_replace_missing_file_fallback_and_force_delete(): void
    {
        Storage::fake('public');

        $category = Category::firstOrFail();
        $unit = Unit::firstOrFail();

        // 1. Unggah foto saat membuat barang baru
        $this->actingAs($this->staff)
            ->post(route('products.store'), [
                'kode_barang' => 'BRG-0099',
                'nama' => 'Monitor LED 24 Inci',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'harga_beli' => 1750000,
                'stok_minimum' => 2,
                'lokasi_rak' => 'D-01',
                'foto' => UploadedFile::fake()->image('monitor-lama.jpg', 400, 400),
            ])
            ->assertRedirect(route('products.index'));

        $product = Product::where('kode_barang', 'BRG-0099')->firstOrFail();
        $oldPhotoPath = $product->foto;

        $this->assertNotNull($oldPhotoPath);
        $this->assertStringStartsWith('products/', $oldPhotoPath);
        $this->assertNotSame('products/monitor-lama.jpg', $oldPhotoPath);
        Storage::disk('public')->assertExists($oldPhotoPath);
        $this->assertSame(Storage::disk('public')->url($oldPhotoPath), $product->foto_url);

        // Pastikan URL foto tampil di halaman detail dan form ubah
        $this->actingAs($this->staff)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($product->foto_url, false);

        $this->actingAs($this->staff)
            ->get(route('products.edit', $product))
            ->assertOk()
            ->assertSee($product->foto_url, false);

        // 2. Ganti foto pada form ubah -> foto lama terhapus, foto baru tersimpan
        $this->actingAs($this->staff)
            ->put(route('products.update', $product), [
                'kode_barang' => 'BRG-0099',
                'nama' => 'Monitor LED 24 Inci IPS',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'harga_beli' => 1850000,
                'stok_minimum' => 2,
                'lokasi_rak' => 'D-01',
                'foto' => UploadedFile::fake()->image('monitor-baru.png', 500, 500),
            ])
            ->assertRedirect(route('products.index'));

        $product->refresh();
        $newPhotoPath = $product->foto;

        $this->assertNotSame($oldPhotoPath, $newPhotoPath);
        Storage::disk('public')->assertMissing($oldPhotoPath);
        Storage::disk('public')->assertExists($newPhotoPath);
        $this->assertSame(Storage::disk('public')->url($newPhotoPath), $product->foto_url);

        // 3. Bila file fisik tidak ada di disk public, foto_url bernilai null & tampil ikon placeholder (bukan img rusak)
        Storage::disk('public')->delete($newPhotoPath);
        $this->assertNull($product->fresh()->foto_url);

        $this->actingAs($this->staff)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertDontSee('alt="Monitor LED 24 Inci IPS"', false);

        // 4. Unggah ulang lalu hapus permanen (forceDelete) -> file foto ikut terhapus dari disk public
        $this->actingAs($this->staff)
            ->put(route('products.update', $product), [
                'kode_barang' => 'BRG-0099',
                'nama' => 'Monitor LED 24 Inci IPS',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'harga_beli' => 1850000,
                'stok_minimum' => 2,
                'foto' => UploadedFile::fake()->image('monitor-final.jpg', 400, 400),
            ]);

        $product->refresh();
        $finalPhotoPath = $product->foto;
        Storage::disk('public')->assertExists($finalPhotoPath);

        $product->forceDelete();
        Storage::disk('public')->assertMissing($finalPhotoPath);
    }

    public function test_tc35_and_tc37_xss_escaped_and_fake_php_image_rejected(): void
    {
        Storage::fake('public');

        $category = Category::firstOrFail();
        $unit = Unit::firstOrFail();

        // TC-35: Nama barang berisi tag <script> disimpan dan di-escape otomatis di HTML (tidak dieksekusi)
        $xssName = '<script>alert(1)</script> Kertas Uji';
        $this->actingAs($this->staff)
            ->post(route('products.store'), [
                'kode_barang' => 'BRG-XSS1',
                'nama' => $xssName,
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'harga_beli' => 10000,
                'stok_minimum' => 5,
            ])
            ->assertRedirect(route('products.index'));

        $product = Product::where('kode_barang', 'BRG-XSS1')->firstOrFail();

        $this->actingAs($this->staff)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt; Kertas Uji', false)
            ->assertDontSee('<script>alert(1)</script>', false);

        // TC-37: Unggah berkas .php yang diganti namanya menjadi .jpg -> ditolak validasi image/mimes
        $fakePhpFile = UploadedFile::fake()->create('exploit.jpg', 12, 'application/x-php');

        $this->actingAs($this->staff)
            ->post(route('products.store'), [
                'kode_barang' => 'BRG-PHP1',
                'nama' => 'Barang Berkas Palsu',
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'stok_minimum' => 2,
                'foto' => $fakePhpFile,
            ])
            ->assertSessionHasErrors(['foto']);

        $this->assertDatabaseMissing('products', ['kode_barang' => 'BRG-PHP1']);
    }
}
