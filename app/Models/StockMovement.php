<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'tipe',
        'referensi_tipe',
        'referensi_id',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'stok_sebelum' => 'integer',
            'stok_sesudah' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stockIn(): BelongsTo
    {
        return $this->belongsTo(StockIn::class, 'referensi_id');
    }

    public function stockOut(): BelongsTo
    {
        return $this->belongsTo(StockOut::class, 'referensi_id');
    }

    /**
     * Nomor transaksi sumber (contoh: BM-20261102-001 atau BK-20261102-001).
     */
    public function getNomorReferensiAttribute(): string
    {
        if ($this->referensi_tipe === 'stock_in') {
            return $this->stockIn?->no_transaksi ?? 'BM-#'.$this->referensi_id;
        }

        if ($this->referensi_tipe === 'stock_out') {
            return $this->stockOut?->no_transaksi ?? 'BK-#'.$this->referensi_id;
        }

        return strtoupper((string) $this->referensi_tipe).' #'.$this->referensi_id;
    }

    /**
     * URL menuju halaman detail transaksi sumber.
     */
    public function getUrlReferensiAttribute(): ?string
    {
        if ($this->referensi_tipe === 'stock_in' && $this->stockIn) {
            return route('stock-ins.show', $this->referensi_id);
        }

        if ($this->referensi_tipe === 'stock_out' && $this->stockOut) {
            return route('stock-outs.show', $this->referensi_id);
        }

        return null;
    }
}
