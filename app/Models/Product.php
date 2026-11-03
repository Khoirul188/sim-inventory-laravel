<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

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
}
