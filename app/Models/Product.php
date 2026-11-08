<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        // Hapus file foto dari disk public saat barang dihapus permanen
        static::forceDeleted(function (Product $product): void {
            if ($product->foto) {
                Storage::disk('public')->delete($product->foto);
            }
        });
    }

    protected $fillable = [
        'kode_barang',
        'nama',
        'category_id',
        'unit_id',
        'harga_beli',
        'stok',
        'stok_minimum',
        'lokasi_rak',
        'foto',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'harga_beli' => 'decimal:2',
            'stok' => 'integer',
            'stok_minimum' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stockInItems(): HasMany
    {
        return $this->hasMany(StockInItem::class);
    }

    public function stockOutItems(): HasMany
    {
        return $this->hasMany(StockOutItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Scope barang dengan stok menipis (termasuk habis: stok <= stok_minimum).
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stok', '<=', 'stok_minimum');
    }

    /**
     * Scope barang dengan stok habis (stok = 0).
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stok', 0);
    }

    /**
     * Satu-satunya sumber logika status stok (BR-07):
     * habis jika stok = 0, menipis jika stok <= stok_minimum, selain itu aman.
     */
    public function getStatusStokAttribute(): string
    {
        if ($this->stok === 0) {
            return 'habis';
        }

        if ($this->stok <= $this->stok_minimum) {
            return 'menipis';
        }

        return 'aman';
    }

    /**
     * Satu-satunya sumber URL foto barang dari disk public.
     * Mengembalikan null bila kolom foto kosong atau file fisik tidak ditemukan di storage.
     * Menggunakan asset() agar mengikuti host/base path dari request aktif (Apache maupun php artisan serve).
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto || ! Storage::disk('public')->exists($this->foto)) {
            return null;
        }

        return asset('storage/'.$this->foto);
    }

    /**
     * Buat kode barang otomatis berurutan (BRG-0001, BRG-0002, dst.)
     * dengan tetap memperhitungkan barang yang sudah di-soft delete (FR-15, BR-08).
     */
    public static function generateKodeBarang(): string
    {
        $codes = static::withTrashed()
            ->where('kode_barang', 'like', 'BRG-%')
            ->pluck('kode_barang');

        $maxNumber = 0;
        foreach ($codes as $code) {
            if (preg_match('/^BRG-(\d+)$/', (string) $code, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        $next = $maxNumber + 1;

        do {
            $candidate = 'BRG-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (static::withTrashed()->where('kode_barang', $candidate)->exists());

        return $candidate;
    }
}
