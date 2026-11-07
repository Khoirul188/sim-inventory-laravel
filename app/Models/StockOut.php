<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_transaksi',
        'tanggal',
        'tujuan',
        'alasan',
        'user_id',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockOutItem::class);
    }

    /**
     * Label bahasa Indonesia untuk alasan barang keluar.
     */
    public static function alasanLabels(): array
    {
        return [
            'pemakaian' => 'Pemakaian',
            'penjualan' => 'Penjualan',
            'rusak' => 'Rusak',
            'koreksi' => 'Koreksi',
            'lainnya' => 'Lainnya',
        ];
    }

    public function getAlasanLabelAttribute(): string
    {
        return self::alasanLabels()[$this->alasan] ?? ucfirst((string) $this->alasan);
    }
}
